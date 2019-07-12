<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\VarLikeIdentifier;

class Variable extends AbstractPrefix
{
    /**
     * @return Expr\Variable
     */
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);
        $node = new Expr\Variable($name);
        $parser->setAttributes($node, $token, $token);

        return $node;
    }

    public function parseIdentifier(ParserStateInterface $parser): VarLikeIdentifier
    {
        $var = $this->parse($parser);
        assert($var instanceof Expr\Variable && is_string($var->name));
        $node = new VarLikeIdentifier($var->name);
        $parser->setAttributes($node, $var, $var);

        return $node;
    }
}
