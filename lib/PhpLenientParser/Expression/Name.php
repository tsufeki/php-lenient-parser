<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Name as NameNode;
use PhpParser\Parser\Tokens;

class Name extends AbstractPrefix
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return NameNode|null
     */
    public function parse(ParserStateInterface $parser)
    {
        //TODO: relative name
        $parts = [];
        $first = $parser->lookAhead();
        $fullyQualified = null !== $parser->eat(Tokens::T_NS_SEPARATOR);

        do {
            $token = $parser->eat(Tokens::T_STRING);
            if ($token !== null) {
                $parts[] = $token->value;
            }

            $sep = $parser->eat(Tokens::T_NS_SEPARATOR);
        } while ($sep !== null);

        $name = $fullyQualified ? new NameNode\FullyQualified($parts) : new NameNode($parts);

        return $parser->setAttributes($name, $first, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return NameNode|null
     */
    public function parserOrNull(ParserStateInterface $parser)
    {
        if (in_array($parser->lookAhead()->type, [Tokens::T_STRING, Tokens::T_NS_SEPARATOR])) {
            return $this->parse($parser);
        }

        return null;
    }
}
