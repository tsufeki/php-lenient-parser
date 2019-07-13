<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Property implements StatementInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var Type
     */
    private $typeParser;

    public function __construct(Variable $variableParser, Type $typeParser)
    {
        $this->variableParser = $variableParser;
        $this->typeParser = $typeParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $parser->eatIf(Tokens::T_VAR);
        $type = $this->typeParser->parse($parser);
        $props = [];

        while (($first = $parser->lookAhead())->type === $this->variableParser->getToken()) {
            $var = $this->variableParser->parseIdentifier($parser);
            $expr = null;
            if ($parser->eatIf(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            $prop = new Node\Stmt\PropertyProperty($var, $expr);
            $parser->setAttributes($prop, $first, $parser->last());
            $props[] = $prop;

            if ($parser->isNext(ord(';')) || !$parser->assert(ord(','))) {
                break;
            }
        }

        if ($type === null && $props === []) {
            return null;
        }
        $parser->assert(ord(';'));

        $node = new Node\Stmt\Property(0, $props, [], $type);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return null;
    }
}
