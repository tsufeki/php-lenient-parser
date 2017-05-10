<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\VarLikeIdentifier;

class Variable extends AbstractPrefix
{
    /**
     * @param ParserStateInterface $parser
     *
     * @return Expr\Variable
     */
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);

        return $parser->setAttributes(new Expr\Variable($name), $token, $token);
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return VarLikeIdentifier|string
     */
    public function parseIdentifier(ParserStateInterface $parser)
    {
        /** @var Expr\Variable $var */
        $var = $this->parse($parser);
        if ($parser->getOption('v3compat')) {
            return $var->name;
        }

        return $parser->setAttributes(new VarLikeIdentifier($var->name), $var, $var);
    }
}
