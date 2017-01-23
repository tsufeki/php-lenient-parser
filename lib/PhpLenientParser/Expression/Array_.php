<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Array_ extends AbstractPrefix
{
    /**
     * @var int|null
     */
    private $secondToken;

    /**
     * @var int
     */
    private $endToken;

    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @var int|null
     */
    private $kind;

    /**
     * @param int $token
     * @param int|null $secondToken
     * @param int $endToken
     * @param string $nodeClass
     * @param int|null $kind
     */
    public function __construct($token, $secondToken, $endToken, $nodeClass, $kind = null)
    {
        parent::__construct($token);
        $this->secondToken = $secondToken;
        $this->endToken = $endToken;
        $this->nodeClass = $nodeClass;
        $this->kind = $kind;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        if ($this->secondToken) {
            $parser->assert($this->secondToken);
        }
        $items = [];

        while ($parser->lookAhead()->type !== $this->endToken) {
            $first = $parser->lookAhead();
            $key = null;
            $ref = $parser->eat(ord('&')) !== null;
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null && !in_array(
                $parser->lookAhead()->type,
                [ord(','), Tokens::T_DOUBLE_ARROW, $this->endToken]
            )) {
                break;
            }

            if ($parser->eat(Tokens::T_DOUBLE_ARROW) !== null) {
                $key = $expr;
                $ref = $parser->eat(ord('&')) !== null;
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            if ($key === null && $expr === null) {
                $items[] = null;
            } else {
                $items[] = $parser->setAttributes(new Node\Expr\ArrayItem($expr, $key, $ref), $first, $parser->last());
            }
            $parser->eat(ord(','));
        }

        $parser->assert($this->endToken);
        $class = $this->nodeClass;
        /** @var Node */
        $node = new $class($items);
        if ($this->kind) {
            $node->setAttribute('kind', $this->kind);
        }

        return $parser->setAttributes($node, $token, $parser->last());
    }
}
