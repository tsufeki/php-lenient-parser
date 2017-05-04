<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Name;

class NameSpecial extends AbstractPrefix
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Name
     */
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        return $parser->setAttributes(new Name([$token->value]), $token, $token);
    }
}
