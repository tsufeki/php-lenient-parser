<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;
use PhpParser\Parser\Tokens;

class NameOrConst extends Name
{
    public function parse(ParserStateInterface $parser)
    {
        $name = parent::parse($parser);
        if ($name === null) {
            return null;
        }

        switch ($parser->lookAhead()->type) {
            case Tokens::T_PAAMAYIM_NEKUDOTAYIM:
            case ord('('):
                return $name;
            default:
                return $parser->setAttributes(new Expr\ConstFetch($name), $name, $name);
        }
    }
}
