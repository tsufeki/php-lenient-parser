<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Postfix extends AbstractOperator implements InfixInterface
{
    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $class = $this->getNodeClass();
        /** @var Node\Expr */
        $node = new $class($left);
        $parser->setAttributes($node, $left, $token);

        return $node;
    }
}
