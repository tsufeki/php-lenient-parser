<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\VarLikeIdentifier;

class Variable extends AbstractPrefix
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);

        return $parser->setAttributes(new Expr\Variable($name), $token, $token);
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return VarLikeIdentifier
     */
    public function parseIdentifier(ParserStateInterface $parser): VarLikeIdentifier
    {
        /** @var Expr\Variable $var */
        $var = $this->parse($parser);

        return $parser->setAttributes(new VarLikeIdentifier($var->name), $var, $var);
    }
}
