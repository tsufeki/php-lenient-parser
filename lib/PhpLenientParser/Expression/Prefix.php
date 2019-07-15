<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Prefix extends AbstractPrefix
{
    /**
     * @var int
     */
    private $precedence;

    /**
     * @var string
     */
    private $nodeClass;

    public function __construct(int $token, int $precedence, string $nodeClass)
    {
        parent::__construct($token);
        $this->precedence = $precedence;
        $this->nodeClass = $nodeClass;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser, $this->precedence);
        /** @var Node\Expr */
        $node = new $this->nodeClass($expr);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }
}
