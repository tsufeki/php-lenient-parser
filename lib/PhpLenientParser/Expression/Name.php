<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Name as NameNode;
use PhpParser\Parser\Tokens;

class Name extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        //TODO: relative name
        $parts = [];
        $first = $parser->lookAhead();
        $fullyQualified = null !== $parser->eat(Tokens::T_NS_SEPARATOR);

        while (true) {
            $token = $parser->assert(Tokens::T_STRING);
            $parts[] = $token !== null ? $token->value : '';

            $sep = $parser->eat(Tokens::T_NS_SEPARATOR);
            if ($sep === null) {
                break;
            }
        }

        $name = $fullyQualified ? new NameNode\FullyQualified($parts) : new NameNode($parts);
        return $parser->setAttributes($name, $first, $parser->last());
    }
}
