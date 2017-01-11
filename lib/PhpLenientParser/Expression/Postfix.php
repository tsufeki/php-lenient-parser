<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

class Postfix extends AbstractOperator implements InfixInterface
{
    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left), $left, $token);
    }
}
