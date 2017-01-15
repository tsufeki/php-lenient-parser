<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;

class Variable extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);

        return $parser->setAttributes(new Expr\Variable($name), $token, $token);
    }
}
