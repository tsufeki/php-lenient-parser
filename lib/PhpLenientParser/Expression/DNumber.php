<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Scalar;

class DNumber extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $value = Scalar\DNumber::parse($token->value);
        return $parser->setAttributes(new Scalar\DNumber($value), $token, $token);
    }
}
