<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;

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

    /**
     * @param int $token
     * @param string $nodeClass
     * @param bool $parensRequired
     * @param int $precedence
     */
    public function __construct(int $token, string $nodeClass, bool $parensRequired = false, int $precedence = 0)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
        $this->parensRequired = $parensRequired;
        $this->precedence = $precedence;
    }

    public function parse(ParserStateInterface $parser)
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
        return $parser->setAttributes(new $nodeClass($expr), $token, $parser->last());
    }
}
