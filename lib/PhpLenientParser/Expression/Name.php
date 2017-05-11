<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Name as NameNode;
use PhpParser\Parser\Tokens;

class Name extends AbstractPrefix
{
    const NORMAL = 1;
    const FULLY_QUALIFIED = 2;
    const RELATIVE = 4;
    const ANY = 7;

    /**
     * @param ParserStateInterface $parser
     * @param int                  $kinds
     * @param bool                 $trailingSep Whether trailing separator is accepted.
     *
     * @return NameNode|null
     */
    public function parse(ParserStateInterface $parser, int $kinds = self::ANY, bool $trailingSep = false)
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
            default:
                if (!($kinds & self::NORMAL)) {
                    return null;
                }
                break;
        }

        $parts = [];
        do {
            $token = $parser->lookAhead();
            $error = $token->type !== Tokens::T_STRING;
            if (!$error) {
                $parser->eat();
                $parts[] = $token->value;
            }

            $sep = $parser->eat(Tokens::T_NS_SEPARATOR);
            if ($error && (!$trailingSep || $sep !== null)) {
                $parser->unexpected($token, Tokens::T_STRING);
            }
        } while ($sep !== null);

        $name = $fullyQualified ? new NameNode\FullyQualified($parts)
            : ($relative ? new NameNode\Relative($parts)
            : new NameNode($parts));

        return $parser->setAttributes($name, $first, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     * @param int                  $kinds
     * @param bool                 $trailingSep Whether trailing separator is accepted.
     *
     * @return NameNode|null
     */
    public function parserOrNull(ParserStateInterface $parser, int $kinds = self::ANY, bool $trailingSep = false)
    {
        if ($parser->isNext(Tokens::T_STRING, Tokens::T_NS_SEPARATOR, Tokens::T_NAMESPACE)) {
            return $this->parse($parser, $kinds, $trailingSep);
        }

        return null;
    }
}
