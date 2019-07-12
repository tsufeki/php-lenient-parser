<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Property implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(int $token, Variable $variableParser)
    {
        $this->token = $token;
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        $parser->eatIf(Tokens::T_VAR);
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

        $parser->assert(ord(';'));
        $node = new Node\Stmt\Property(0, $props);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }
}
