<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class FunctionCall extends AbstractInfix
{
    /**
     * @var ArgumentList
     */
    private $argsParser;

    public function __construct(int $token, int $precedence, ArgumentList $argsParser)
    {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->argsParser = $argsParser;
    }

    /**
     * @param Node\Expr|Node\Name $left
     */
    public function parse(ParserStateInterface $parser, $left): ?Node\Expr
    {
        $args = $this->argsParser->parse($parser);

        return new Node\Expr\FuncCall($left, $args, $parser->getAttributes($left, $parser->last()));
    }
}
