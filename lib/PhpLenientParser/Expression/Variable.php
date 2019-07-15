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

        return new Node\Expr\Variable($name, $parser->getAttributes($token, $token));
    }

    public function parseIdentifier(ParserStateInterface $parser): Node\VarLikeIdentifier
    {
        $var = $this->parse($parser);
        assert($var instanceof Node\Expr\Variable && is_string($var->name));

        return new Node\VarLikeIdentifier($var->name, $parser->getAttributes($var, $var));
    }
}
