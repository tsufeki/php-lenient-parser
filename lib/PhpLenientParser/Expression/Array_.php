<?php declare(strict_types=1);

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

    public function __construct(
        int $token,
        ?int $secondToken,
        int $endToken,
        string $nodeClass,
        ?int $kind = null
    ) {
        parent::__construct($token);
        $this->secondToken = $secondToken;
        $this->endToken = $endToken;
        $this->nodeClass = $nodeClass;
        $this->kind = $kind;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        if ($this->secondToken) {
            $parser->assert($this->secondToken);
        }
        $items = [];

        while (!$parser->isNext($this->endToken)) {
            $first = $parser->lookAhead();
            $key = null;
            $ref = $parser->eatIf(ord('&')) !== null;
            $unpack = !$ref && $parser->eatIf(Tokens::T_ELLIPSIS) !== null;
            $expr = $parser->getExpressionParser()->parse($parser);
            if ($expr === null && !$parser->isNext(ord(','), Tokens::T_DOUBLE_ARROW, $this->endToken)) {
                break;
            }

            if (!$ref && !$unpack && $parser->eatIf(Tokens::T_DOUBLE_ARROW) !== null) {
                $key = $expr;
                $ref = $parser->eatIf(ord('&')) !== null;
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            if ($key === null && $expr === null && !$ref && !$unpack) {
                $items[] = null;
            } else {
                $expr = $expr ?? $parser->getExpressionParser()->makeErrorNode($parser->last());
                $items[] = new Node\Expr\ArrayItem($expr, $key, $ref, $parser->getAttributes($first, $parser->last()), $unpack);
            }
            $parser->eatIf(ord(','));
        }

        $parser->assert($this->endToken);
        $class = $this->nodeClass;
        /** @var Node\Expr */
        $node = new $class($items, $parser->getAttributes($token, $parser->last(), $this->kind ? ['kind' => $this->kind] : []));

        return $node;
    }
}
