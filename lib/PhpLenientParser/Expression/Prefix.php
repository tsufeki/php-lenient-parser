<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Prefix extends AbstractOperator implements PrefixInterface
{
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser, $this->getPrecedence());
        $class = $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($expr);
        $parser->setAttributes($node, $token, $expr);

        return $node;
    }
}
