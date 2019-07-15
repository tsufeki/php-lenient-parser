<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ObjectAccessNew extends AbstractInfix
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

    public function __construct(
        int $token,
        int $precedence,
        Identifier $identifierParser,
        Variable $variableParser,
        IndirectVariable $indirectVariableParser
    ) {
        parent::__construct($token, $precedence, self::LEFT_ASSOCIATIVE);
        $this->identifierParser = $identifierParser;
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
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
            $parser->unexpected($parser->lookAhead(), Tokens::T_STRING, Tokens::T_VARIABLE, ord('{'), ord('$'));
            $name = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }

        $node = new Node\Expr\PropertyFetch($left, $name);
        $parser->setAttributes($node, $left, $parser->last());

        return $node;
    }
}
