<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Statement\ParameterList;
use PhpLenientParser\Statement\Type;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Closure extends AbstractPrefix
{
    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var ParameterList
     */
    private $parametersParser;

    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(int $token, Type $typeParser, ParameterList $parametersParser, Variable $variableParser)
    {
        parent::__construct($token);
        $this->typeParser = $typeParser;
        $this->parametersParser = $parametersParser;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        if (!$this->isClosure($parser)) {
            return null;
        }

        $token = $parser->lookAhead();
        $static = $parser->eatIf(Tokens::T_STATIC) !== null;
        $parser->assert(Tokens::T_FUNCTION);
        $ref = $parser->eatIf(ord('&')) !== null;

        $params = [];
        if ($parser->isNext(ord('('))) {
            $params = $this->parametersParser->parse($parser);
        }

        $uses = [];
        if ($parser->isNext(Tokens::T_USE)) {
            $uses = $this->parseUses($parser);
        }

        $returnType = null;
        if ($parser->eatIf(ord(':')) !== null) {
            $returnType = $this->typeParser->parse($parser);
        }

        $stmts = [];
        if ($parser->assert(ord('{'))) {
            $stmts = $parser->getStatementParser()->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        return $node = new Node\Expr\Closure([
            'static' => $static,
            'byRef' => $ref,
            'params' => $params,
            'uses' => $uses,
            'returnType' => $returnType,
            'stmts' => $stmts,
        ], $parser->getAttributes($token, $parser->last()));
    }

    private function isClosure(ParserStateInterface $parser): bool
    {
        $i = 0;
        if ($parser->lookAhead($i)->type === Tokens::T_STATIC) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== Tokens::T_FUNCTION) {
            return false;
        }
        $i++;
        if ($parser->lookAhead($i)->type === ord('&')) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== ord('(')) {
            return false;
        }

        return true;
    }

    /**
     * @return Node\Expr\ClosureUse[]
     */
    private function parseUses(ParserStateInterface $parser): array
    {
        $parser->eat();
        $parser->assert(ord('('));
        $uses = [];

        while (true) {
            $first = $parser->lookAhead();
            $ref = $parser->eatIf(ord('&')) !== null;
            if (!$parser->isNext($this->variableParser->getToken())) {
                break;
            }
            $var = $this->variableParser->parse($parser);
            assert($var instanceof Node\Expr\Variable);
            $uses[] = new Node\Expr\ClosureUse($var, $ref, $parser->getAttributes($first, $parser->last()));
            $parser->eatIf(ord(','));
        }

        $parser->assert(ord(')'));

        return $uses;
    }
}
