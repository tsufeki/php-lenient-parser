<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Scalar;

class String_ extends AbstractPrefix
{
    const ESCAPES = [
        'n' => "\n",
        'r' => "\r",
        't' => "\t",
        'v' => "\v",
        'e' => "\e",
        'f' => "\f",
        '$' => '$',
    ];

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $start = 0;
        if ($token->value[0] === 'b' || $token->value[0] === 'B') {
            $start = 1;
        }
        if ($token->value[$start] === '\'') {
            $kind = Scalar\String_::KIND_SINGLE_QUOTED;
            $value = substr($token->value, $start + 1, -1);
            $value = self::replaceQuoteEscapes($value, "'");
        } else {
            $kind = Scalar\String_::KIND_DOUBLE_QUOTED;
            $value = substr($token->value, $start + 1, -1);
            $value = self::replaceQuoteEscapes($value, '"');
            $value = self::replaceEscapes($value);
        }

        return $parser->setAttributes(new Scalar\String_($value, ['kind' => $kind]), $token, $token);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function replaceQuoteEscapes($string, $quote)
    {
        return str_replace(
            ['\\\\', '\\' . $quote],
            ['\\', $quote],
            $string
        );
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function replaceEscapes($string)
    {
        $string = preg_replace_callback(
            '/\\\\(?:([nrtvef$])|([0-7]{1,3})|[xX]([0-9a-fA-F]{1,2})|u\\{([0-9a-fA-F]+)\\})/',
            function ($match) {
                if (!empty($match[1])) {
                    return self::ESCAPES[$match[1]];
                } elseif (!empty($match[2])) {
                    return chr(octdec($match[2]));
                } elseif (!empty($match[3])) {
                    return chr(hexdec($match[3]));
                } elseif (!empty($match[4])) {
                    return self::utf8chr(hexdec($match[4]));
                }
            },
            $string
        );

        return $string;
    }

    /**
     * @param int $cp
     *
     * @return string
     */
    public static function utf8chr($cp)
    {
        if ($cp <= 0x7f) {
            return chr($cp);
        }
        if ($cp <= 0x7ff) {
            return chr(($cp>>6) | 0xc0) .
                    chr(($cp&0x3f) | 0x80);
        }
        if ($cp <= 0xffff) {
            return chr(($cp>>12) | 0xe0) .
                    chr((($cp>>6)&0x3f) | 0x80) .
                    chr(($cp&0x3f) | 0x80);
        }
        if ($cp <= 0x1fffff) {
            return chr(($cp>>18) | 0xf0) .
                    chr((($cp>>12)&0x3f) | 0x80) .
                    chr((($cp>>6)&0x3f) | 0x80) .
                    chr(($cp&0x3f) | 0x80);
        }
        return '';
    }
}
