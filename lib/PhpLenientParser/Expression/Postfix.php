<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Postfix extends AbstractInfix
{
    /**
     * @var string
     */
    private $nodeClass;

    public function __construct(
        int $token,
        int $precedence,
        string $nodeClass
    ) {
        parent::__construct($token, $precedence, self::NOT_ASSOCIATIVE);
        $this->nodeClass = $nodeClass;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        /** @var Node\Expr */
        $node = new $this->nodeClass($left);
        $parser->setAttributes($node, $left, $token);

        return $node;
    }
}
