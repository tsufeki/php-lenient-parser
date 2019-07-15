<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class DNumber extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $value = $this->parseDNumber($token->value);

        return new Node\Scalar\DNumber($value, $parser->getAttributes($token, $token));
    }

    private function parseDNumber(string $string): float
    {
        $string = str_replace('_', '', $string);

        if (strpbrk($string, '.eE') !== false) {
            return (float)$string;
        }

        if ($string[0] === '0') {
            if ('x' === $string[1] || 'X' === $string[1]) {
                return hexdec($string);
            }
            if ('b' === $string[1] || 'B' === $string[1]) {
                return bindec($string);
            }

            return octdec(substr($string, 0, strcspn($string, '89')));
        }

        return (float)$string;
    }
}
