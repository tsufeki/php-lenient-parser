<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Variable extends AbstractPrefix
{
    /**
     * @return Node\Expr\Variable
     */
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $name = substr($token->value, 1) ?: $parser->getExpressionParser()->makeErrorNode($token);
        $node = new Node\Expr\Variable($name);
        $parser->setAttributes($node, $token, $token);

        return $node;
    }

    public function parseIdentifier(ParserStateInterface $parser): Node\VarLikeIdentifier
    {
        $var = $this->parse($parser);
        assert($var instanceof Node\Expr\Variable && is_string($var->name));
        $node = new Node\VarLikeIdentifier($var->name);
        $parser->setAttributes($node, $var, $var);

        return $node;
    }
}
