<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Name
{
    public const NORMAL = 1;
    public const FULLY_QUALIFIED = 2;
    public const RELATIVE = 4;
    public const STATIC_ = 8;

    public const NOT_STATIC = 7;
    public const ANY = 15;

    public function parse(ParserStateInterface $parser, int $kinds = self::NOT_STATIC, bool $trailingSep = false): ?Node\Name
    {
        $first = $parser->lookAhead();

        $relative = false;
        $fullyQualified = false;
        switch ($parser->lookAhead()->type) {
            case Tokens::T_NAMESPACE:
                if ($parser->lookAhead(1)->type !== Tokens::T_NS_SEPARATOR) {
                    return null;
                }
                if (!($kinds & self::RELATIVE)) {
                    return null;
                }
                $relative = true;
                $parser->eat();
                $parser->eat();
                break;
            case Tokens::T_NS_SEPARATOR:
                if (!($kinds & self::FULLY_QUALIFIED)) {
                    return null;
                }
                $fullyQualified = true;
                $parser->eat();
                break;
            case Tokens::T_STRING:
                if (!($kinds & self::NORMAL)) {
                    return null;
                }
                break;
            case Tokens::T_STATIC:
                if (!($kinds & self::STATIC_)) {
                    return null;
                }
                $parser->eat();
                $name = new Node\Name([$first->value]);
                $parser->setAttributes($name, $first, $first);

                return $name;
            default:
                return null;
        }

        $parts = [];
        do {
            $token = $parser->lookAhead();
            $error = $token->type !== Tokens::T_STRING;
            if (!$error) {
                $parser->eat();
                $parts[] = $token->value;
            }

            $sep = $parser->eatIf(Tokens::T_NS_SEPARATOR);
            if ($error && (!$trailingSep || $sep !== null)) {
                $parser->unexpected($token, Tokens::T_STRING);
            }
        } while ($sep !== null);

        if ($parts === []) {
            $parts = [''];
        }

        $name = $fullyQualified ? new Node\Name\FullyQualified($parts)
            : ($relative ? new Node\Name\Relative($parts)
            : new Node\Name($parts));
        $parser->setAttributes($name, $first, $parser->last());

        if ($parts === ['']) {
            $name->parts = [];
        }

        return $name;
    }
}
