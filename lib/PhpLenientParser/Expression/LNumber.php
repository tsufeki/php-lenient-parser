<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class LNumber extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        list($value, $kind) = $this->parseLNumber($token->value);

        return $parser->setAttributes(new Node\Scalar\LNumber($value, ['kind' => $kind]), $token, $token);
    }

    /**
     * @param string $string
     *
     * @return array
     */
    private function parseLNumber(string $string): array
    {
        $kind = null;
        $value = 0;

        if ('0' !== $string[0] || '0' === $string) {
            $kind = Node\Scalar\LNumber::KIND_DEC;
            $value = (int) $string;
        } elseif ('x' === $string[1] || 'X' === $string[1]) {
            $kind = Node\Scalar\LNumber::KIND_HEX;
            $value = hexdec($string);
        } elseif ('b' === $string[1] || 'B' === $string[1]) {
            $kind = Node\Scalar\LNumber::KIND_BIN;
            $value = bindec($string);
        } else {
            $kind = Node\Scalar\LNumber::KIND_OCT;
            $value = intval($string, 8);
        }

        return [$value, $kind];
    }
}
