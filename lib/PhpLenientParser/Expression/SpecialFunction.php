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
     * @param int $token
     * @param string $nodeClass
     * @param bool $parensRequired
     */
    public function __construct($token, $nodeClass, $parensRequired = false)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
        $this->parensRequired = $parensRequired;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        if ($this->parensRequired) {
            $parser->assert(ord('('));
        }

        $expr = $parser->getExpressionParser()->parseOrError($parser);
        if ($this->parensRequired) {
            $parser->assert(ord(')'));
        }

        $nodeClass = $this->nodeClass;
        return $parser->setAttributes(new $nodeClass($expr), $token, $parser->last());
    }
}
