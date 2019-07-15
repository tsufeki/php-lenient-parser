<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class String_ extends AbstractPrefix
{
    private const ESCAPES = [
        'n' => "\n",
        'r' => "\r",
        't' => "\t",
        'v' => "\v",
        'e' => "\e",
        'f' => "\f",
        '$' => '$',
    ];

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();

        $start = 0;
        if ($token->value[0] === 'b' || $token->value[0] === 'B') {
            $start = 1;
        }
        if ($token->value[$start] === '\'') {
            $kind = Node\Scalar\String_::KIND_SINGLE_QUOTED;
            $value = substr($token->value, $start + 1, -1);
            $value = self::replaceQuoteEscapes($value, "'");
        } else {
            $kind = Node\Scalar\String_::KIND_DOUBLE_QUOTED;
            $value = substr($token->value, $start + 1, -1);
            $value = self::replaceEscapes($value);
            $value = self::replaceQuoteEscapes($value, '"');
        }

        return new Node\Scalar\String_($value, $parser->getAttributes(
            $token,
            $token,
            ['kind' => $kind]
        ));
    }

    public static function replaceQuoteEscapes(string $string, string $quote): string
    {
        return str_replace(
            ['\\\\', '\\' . $quote],
            ['\\', $quote],
            $string
        );
    }

    public static function replaceBackslashes(string $string): string
    {
        return str_replace(
            '\\\\',
            '\\',
            $string
        );
    }

    public static function replaceEscapes(string $string): string
    {
        $string = preg_replace_callback(
            '/\\\\(?:(\\\\)|([nrtvef$])|([0-7]{1,3})|[xX]([0-9a-fA-F]{1,2})|u\\{([0-9a-fA-F]+)\\})/',
            function ($match) {
                if (isset($match[1][0])) {
                    return '\\\\';
                }
                if (isset($match[2][0])) {
                    return self::ESCAPES[$match[2]];
                }
                if (isset($match[3][0])) {
                    return chr(octdec($match[3]));
                }
                if (isset($match[4][0])) {
                    return chr((int)hexdec($match[4]));
                }
                if (isset($match[5][0])) {
                    $cp = hexdec($match[5]);
                    if (!is_int($cp)) {
                        return '';
                    }

                    return self::utf8chr($cp);
                }
            },
            $string
        );
        assert($string !== null);

        return $string;
    }

    public static function utf8chr(int $cp): string
    {
        if ($cp <= 0x7f) {
            return chr($cp);
        }
        if ($cp <= 0x7ff) {
            return chr(($cp >> 6) | 0xc0) .
                    chr(($cp & 0x3f) | 0x80);
        }
        if ($cp <= 0xffff) {
            return chr(($cp >> 12) | 0xe0) .
                    chr((($cp >> 6) & 0x3f) | 0x80) .
                    chr(($cp & 0x3f) | 0x80);
        }
        if ($cp <= 0x10ffff) {
            return chr(($cp >> 18) | 0xf0) .
                    chr((($cp >> 12) & 0x3f) | 0x80) .
                    chr((($cp >> 6) & 0x3f) | 0x80) .
                    chr(($cp & 0x3f) | 0x80);
        }

        return '';
    }
}
