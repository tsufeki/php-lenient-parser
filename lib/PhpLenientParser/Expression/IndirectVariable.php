<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class IndirectVariable extends AbstractPrefix
{
    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(int $token, Variable $variableParser)
    {
        parent::__construct($token);
        $this->variableParser = $variableParser;
    }

    /**
     * @return Node\Expr\Variable
     */
    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        switch ($parser->lookAhead()->type) {
            case ord('{'):
                $parser->eat();
                $name = $parser->getExpressionParser()->parseOrError($parser);
                $parser->assert(ord('}'));
                break;
            case $this->variableParser->getToken():
                $name = $this->variableParser->parse($parser);
                break;
            case $this->getToken():
                $name = $this->parse($parser);
                break;
            default:
                $name = $parser->getExpressionParser()->makeErrorNode($token);
        }

        assert($name !== null);
        $node = new Node\Expr\Variable($name);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }
}
