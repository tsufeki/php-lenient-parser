<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node\Expr;
use PhpLenientParser\ParserStateInterface;

class Variable extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);

        return $parser->setAttributes(new Expr\Variable($name), $token, $token);
    }
}
