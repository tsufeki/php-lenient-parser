<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr\Error;

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

    public function parse(ParserStateInterface $parser, $precedence = 0)
    {
        $token = $parser->lookAhead();
        if (!isset($this->prefix[$token->type])) {
            return null;
        }

        $left = $this->prefix[$token->type]->parse($parser);
        while (true) {
            $token = $parser->lookAhead();
            $infix = isset($this->infix[$token->type]) ? $this->infix[$token->type] : null;
            if ($infix !== null && $precedence < $infix->getPrecedence()) {
                $left = $infix->parse($parser, $left);
            } else {
                break;
            }
        }

        return $left;
    }

    public function makeErrorNode($last)
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

        return new Error($attrs);
    }

    public function addPrefix(PrefixInterface $prefix)
    {
        $this->prefix[$prefix->getToken()] = $prefix;
    }

    public function addInfix(InfixInterface $infix)
    {
        $this->infix[$infix->getToken()] = $infix;
    }
}
