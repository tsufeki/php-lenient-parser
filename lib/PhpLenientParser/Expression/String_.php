<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Scalar;

class String_ extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        if ($token->value[0] === 'b' || $token->value[0] === 'B') {
            $kind = $token->value[1] === '\'' ? Scalar\String_::KIND_SINGLE_QUOTED : Scalar\String_::KIND_DOUBLE_QUOTED;
        } else {
            $kind = $token->value[0] === '\'' ? Scalar\String_::KIND_SINGLE_QUOTED : Scalar\String_::KIND_DOUBLE_QUOTED;
        }

        $value = Scalar\String_::parse($token->value);
        return $parser->setAttributes(new Scalar\String_($value, ['kind' => $kind]), $token, $token);
    }
}
