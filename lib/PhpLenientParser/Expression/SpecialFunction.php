<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class SpecialFunction extends AbstractPrefix
{
    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @var bool
     */
    private $parensRequired;

    /**
     * @var int
     */
    private $precedence;

    public function __construct(int $token, string $nodeClass, bool $parensRequired = false, int $precedence = 0)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
        $this->parensRequired = $parensRequired;
        $this->precedence = $precedence;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        if ($this->parensRequired) {
            $parser->assert(ord('('));
        }

        $expr = $parser->getExpressionParser()->parseOrError($parser, $this->precedence);
        if ($this->parensRequired) {
            $parser->assert(ord(')'));
        }

        $nodeClass = $this->nodeClass;
        /** @var Node\Expr */
        $node = new $nodeClass($expr);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }
}
