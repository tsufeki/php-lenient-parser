<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Nullary extends AbstractPrefix
{
    /**
     * @var string
     */
    private $nodeClass;

    public function __construct(int $token, string $nodeClass)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $class = $this->nodeClass;
        /** @var Node\Expr */
        $node = new $class($parser->getAttributes($token, $token));

        return $node;
    }
}
