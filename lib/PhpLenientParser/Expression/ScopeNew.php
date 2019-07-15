<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ScopeNew extends AbstractInfix
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var IndirectVariable
     */
    private $indirectVariableParser;

    public function __construct(
        int $token,
        int $precedence,
        Variable $variableParser,
        IndirectVariable $indirectVariableParser
    ) {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
    }

    /**
     * @param Node\Expr|Node\Name $left
     */
    public function parse(ParserStateInterface $parser, $left): ?Node\Expr
    {
        $parser->eat();
        $var = null;

        switch ($parser->lookAhead()->type) {
            case $this->variableParser->getToken():
                $var = $this->variableParser->parse($parser);
                assert($var instanceof Node\Expr\Variable);
                $name = $var->name;
                break;
            case $this->indirectVariableParser->getToken():
                $var = $this->indirectVariableParser->parse($parser);
                assert($var instanceof Node\Expr\Variable);
                $name = $var->name;
                break;
            default:
                $parser->unexpected($parser->lookAhead(), $this->variableParser->getToken(), $this->indirectVariableParser->getToken());
                $name = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        if (is_string($name)) {
            assert($var !== null);
            $name = new Node\VarLikeIdentifier($name, $parser->getAttributes($var, $var));
        }

        return new Node\Expr\StaticPropertyFetch($left, $name, $parser->getAttributes($left, $parser->last()));
    }
}
