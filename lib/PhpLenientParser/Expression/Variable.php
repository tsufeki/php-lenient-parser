<?php

namespace PhpLenientParser\Expression;

use PhpParser\Parser\Tokens;
use PhpParser\Node\Expr;
use PhpLenientParser\ParserStateInterface;

class Variable extends AbstractAtom
{
    public function __construct()
    {
        parent::__construct(Tokens::T_VARIABLE);
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $name = substr($token->value, 1);

        return $parser->setAttributes(new Expr\Variable($name), $token, $token);
    }
}
