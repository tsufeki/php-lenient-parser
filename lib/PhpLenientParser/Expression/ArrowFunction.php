<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Statement\ParameterList;
use PhpLenientParser\Statement\Type;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ArrowFunction extends AbstractPrefix
{
    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var ParameterList
     */
    private $parametersParser;

    public function __construct(int $token, Type $typeParser, ParameterList $parametersParser)
    {
        parent::__construct($token);
        $this->typeParser = $typeParser;
        $this->parametersParser = $parametersParser;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        if (!$this->isArrow($parser)) {
            return null;
        }

        $token = $parser->lookAhead();
        $static = $parser->eatIf(Tokens::T_STATIC) !== null;
        $parser->assert(Tokens::T_FN);
        $ref = $parser->eatIf(ord('&')) !== null;

        $params = [];
        if ($parser->isNext(ord('('))) {
            $params = $this->parametersParser->parse($parser);
        }

        $returnType = null;
        if ($parser->eatIf(ord(':')) !== null) {
            $returnType = $this->typeParser->parse($parser);
        }

        if ($parser->assert(Tokens::T_DOUBLE_ARROW)) {
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null) {
                $parser->unexpected($parser->lookAhead());
                $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
            }
        } else {
            $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        return new Node\Expr\ArrowFunction([
            'static' => $static,
            'byRef' => $ref,
            'params' => $params,
            'returnType' => $returnType,
            'expr' => $expr,
        ], $parser->getAttributes($token, $parser->last()));
    }

    private function isArrow(ParserStateInterface $parser): bool
    {
        $i = 0;
        if ($parser->lookAhead($i)->type === Tokens::T_STATIC) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== Tokens::T_FN) {
            return false;
        }

        return true;
    }
}
