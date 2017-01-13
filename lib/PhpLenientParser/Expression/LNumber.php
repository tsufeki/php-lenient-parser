<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Scalar;

class LNumber extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $node = Scalar\LNumber::fromString($token->value, [], true);
        return $parser->setAttributes($node, $token, $token);
    }
}
