<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Scalar;

class String_ extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $value = Scalar\String_::parse($token->value);
        return $parser->setAttributes(new Scalar\String_($value), $token, $token);
    }
}
