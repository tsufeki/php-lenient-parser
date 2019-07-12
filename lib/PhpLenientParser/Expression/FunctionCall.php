<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class FunctionCall extends AbstractOperator implements InfixInterface
{
    /**
     * @var ArgumentList
     */
    private $argsParser;

    public function __construct(int $token, int $precedence, ArgumentList $argsParser)
    {
        parent::__construct($token, $precedence, Node\Expr\FuncCall::class);
        $this->argsParser = $argsParser;
    }

    /**
     * @param Node\Expr|Node\Name $left
     */
    public function parse(ParserStateInterface $parser, $left): ?Node\Expr
    {
        $args = $this->argsParser->parse($parser);
        $node = new Node\Expr\FuncCall($left, $args);
        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
