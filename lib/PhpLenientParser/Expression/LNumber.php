<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class LNumber extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        list($value, $kind) = $this->parseLNumber($token->value);

        return new Node\Scalar\LNumber($value, $parser->getAttributes($token, $token, ['kind' => $kind]));
    }

    /**
     * @return int[]
     */
    private function parseLNumber(string $string): array
    {
        $string = str_replace('_', '', $string);
        $kind = null;
        $value = 0;

        if ('0' !== $string[0] || '0' === $string) {
            $kind = Node\Scalar\LNumber::KIND_DEC;
            $value = (int)$string;
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

        assert(is_int($value));

        return [$value, $kind];
    }
}
