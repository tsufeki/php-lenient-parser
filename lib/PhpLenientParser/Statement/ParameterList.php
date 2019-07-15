<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ParameterList
{
    /**
     * @var Type
     */
    private $typeParser;

    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(Type $typeParser, Variable $variableParser)
    {
        $this->typeParser = $typeParser;
        $this->variableParser = $variableParser;
    }

    /**
     * @return Node\Param[]
     */
    public function parse(ParserStateInterface $parser): array
    {
        $parser->eat();
        $params = [];

        while (!$parser->isNext(ord(')'))) {
            $first = $parser->lookAhead();

            $type = $this->typeParser->parse($parser);
            $ref = $parser->eatIf(ord('&')) !== null;
            $variadic = $parser->eatIf(Tokens::T_ELLIPSIS) !== null;

            $var = null;
            $varLast = $parser->last();
            if ($parser->isNext($this->variableParser->getToken())) {
                $var = $this->variableParser->parse($parser);
                assert($var instanceof Node\Expr\Variable);
            }

            $expr = null;
            if ($parser->eatIf(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            if ($var === null && ($type !== null || $expr !== null || $ref || $variadic)) {
                $errorNode = $parser->getExpressionParser()->makeErrorNode($varLast);
                $var = new Node\Expr\Variable($errorNode, $parser->getAttributes($errorNode, $errorNode));
            }

            if ($var !== null) {
                $params[] = $param = new Node\Param(
                    $var,
                    $expr,
                    $type,
                    $ref,
                    $variadic,
                    $parser->getAttributes($first, $parser->last())
                );

                if ($param->variadic && $param->default !== null) {
                    $parser->addError('Variadic parameter cannot have a default value', $param->default->getAttributes());
                }
            }

            if ($parser->eatIf(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(')'));

        return $params;
    }
}
