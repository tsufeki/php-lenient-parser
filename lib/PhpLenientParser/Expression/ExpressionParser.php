<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;
use PhpParser\Node;

class ExpressionParser implements ExpressionParserInterface
{
    /**
     * @var PrefixInterface[]
     */
    private $prefix = [];

    /**
     * @var InfixInterface[]
     */
    private $infix = [];

    public function parse(ParserStateInterface $parser, int $precedence = 0): ?Node\Expr
    {
        $token = $parser->lookAhead();
        if (!isset($this->prefix[$token->type])) {
            return null;
        }

        $left = $this->prefix[$token->type]->parse($parser);

        return $this->parseInfix($parser, $left, $precedence, $token);
    }

    public function parseInfix(ParserStateInterface $parser, ?Node\Expr $left, int $precedence = 0, ?Token $firstToken = null): ?Node\Expr
    {
        if ($left === null) {
            return null;
        }

        $leftPrecedence = null;
        $leftAssociativity = null;
        while (true) {
            $token = $parser->lookAhead();
            $infix = $this->infix[$token->type] ?? null;
            if ($infix !== null && $left !== null && $precedence < $infix->getPrecedence()) {
                if ($leftPrecedence === $infix->getPrecedence() && $leftAssociativity === InfixInterface::NOT_ASSOCIATIVE) {
                    $parser->unexpected($token);
                }

                $left = $infix->parse($parser, $left);
                if ($firstToken !== null && $left !== null) {
                    $left->setAttributes($parser->getAttributes($firstToken, $left, $left->getAttributes()));
                }

                $leftPrecedence = $infix->getPrecedence();
                $leftAssociativity = $infix->getAssociativity();
            } else {
                break;
            }
        }

        return $left;
    }

    public function makeErrorNode($last): Node\Expr\Error
    {
        $lastAttrs = $last->getAttributes();
        $attrs = [];
        if (isset($lastAttrs['endLine'])) {
            $attrs['startLine'] = $lastAttrs['endLine'];
            $attrs['endLine'] = $lastAttrs['endLine'];
        }
        if (isset($lastAttrs['endTokenPos'])) {
            $attrs['startTokenPos'] = $lastAttrs['endTokenPos'] + 1;
            $attrs['endTokenPos'] = $lastAttrs['endTokenPos'];
        }
        if (isset($lastAttrs['endFilePos'])) {
            $attrs['startFilePos'] = $lastAttrs['endFilePos'] + 1;
            $attrs['endFilePos'] = $lastAttrs['endFilePos'];
        }

        return new Node\Expr\Error($attrs);
    }

    public function parseOrError(ParserStateInterface $parser, int $precedence = 0): Node\Expr
    {
        $expr = $this->parse($parser, $precedence);
        if ($expr === null) {
            $parser->unexpected($parser->lookAhead());
            $expr = $this->makeErrorNode($parser->last());
        }

        return $expr;
    }

    public function parseList(ParserStateInterface $parser): array
    {
        $expressions = [];
        while (true) {
            $expr = $this->parse($parser);
            if ($expr !== null) {
                $expressions[] = $expr;
            }
            if ($parser->eatIf(ord(',')) === null) {
                break;
            }
        }

        return $expressions;
    }

    public function addPrefix(PrefixInterface $prefix): void
    {
        $this->prefix[$prefix->getToken()] = $prefix;
    }

    public function addInfix(InfixInterface $infix): void
    {
        $this->infix[$infix->getToken()] = $infix;
    }
}
