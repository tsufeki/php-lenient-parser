<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Scope extends AbstractInfix
{
    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var IndirectVariable
     */
    private $indirectVariableParser;

    /**
     * @var ArgumentList
     */
    private $argsParser;

    public function __construct(
        int $token,
        int $precedence,
        Identifier $identifierParser,
        Variable $variableParser,
        IndirectVariable $indirectVariableParser,
        ArgumentList $argsParser
    ) {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
        $this->argsParser = $argsParser;
    }

    /**
     * @param Node\Expr|Node\Name $left
     */
    public function parse(ParserStateInterface $parser, $left): ?Node\Expr
    {
        $parser->eat();
        /** @var Node\Identifier|null */
        $id = null;
        /** @var Node\Expr\Variable|null */
        $var = null;
        /** @var Node\Expr|null */
        $expr = null;

        switch ($parser->lookAhead()->type) {
            case $this->variableParser->getToken():
                $var = $this->variableParser->parse($parser);
                break;
            case $this->indirectVariableParser->getToken():
                $var = $this->indirectVariableParser->parse($parser);
                break;
            case ord('{'):
                $parser->eat();
                $expr = $parser->getExpressionParser()->parseOrError($parser);
                $parser->assert(ord('}'));
                break;
            default:
                $id = $this->identifierParser->parse($parser);
        }

        if ($parser->isNext(ord('('))) {
            $args = $this->argsParser->parse($parser);
            $name = $id ?? $var ?? $expr ?? $parser->getExpressionParser()->makeErrorNode($parser->last());
            $node = new Node\Expr\StaticCall($left, $name, $args, $parser->getAttributes($left, $parser->last()));
        } elseif ($expr !== null) {
            $node = new Node\Expr\StaticCall($left, $expr, [], $parser->getAttributes($left, $parser->last()));
        } elseif ($var !== null) {
            assert($var instanceof Node\Expr\Variable);
            $name = $var->name;
            if (is_string($name)) {
                $name = new Node\VarLikeIdentifier($name, $parser->getAttributes($var, $var));
            }
            $node = new Node\Expr\StaticPropertyFetch($left, $name, $parser->getAttributes($left, $parser->last()));
        } else {
            $name = $id ?? $parser->getExpressionParser()->makeErrorNode($parser->last());
            $node = new Node\Expr\ClassConstFetch($left, $name, $parser->getAttributes($left, $parser->last()));
        }

        return $node;
    }
}
