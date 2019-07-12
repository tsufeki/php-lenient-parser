<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class ObjectAccess extends AbstractOperator implements InfixInterface
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
        parent::__construct($token, $precedence, '');
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
        $this->argsParser = $argsParser;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $parser->eat();
        $name = null;

        switch ($parser->lookAhead()->type) {
            case $this->variableParser->getToken():
                $name = $this->variableParser->parse($parser);
                break;
            case $this->indirectVariableParser->getToken():
                $name = $this->indirectVariableParser->parse($parser);
                break;
            case ord('{'):
                $parser->eat();
                $name = $parser->getExpressionParser()->parseOrError($parser);
                $parser->assert(ord('}'));
                break;
            default:
                $name = $this->identifierParser->parse($parser);
        }

        if ($name === null) {
            $name = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        if ($parser->isNext(ord('('))) {
            $args = $this->argsParser->parse($parser);
            $node = new Node\Expr\MethodCall($left, $name, $args);
        } else {
            $node = new Node\Expr\PropertyFetch($left, $name);
        }

        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
