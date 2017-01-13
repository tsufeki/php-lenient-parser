<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;

class Nullary extends AbstractPrefix
{
    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @param int $token
     * @param string $nodeClass
     */
    public function __construct($token, $nodeClass)
    {
        parent::__construct($token);
        $this->nodeClass = $nodeClass;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $class = $this->nodeClass;
        return $parser->setAttributes(new $class(), $token, $token);
    }
}
