<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Node\Expr;

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
     * @return Expr\Variable
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
        $node = new Expr\Variable($name);
        $parser->setAttributes($node, $token, $name);

        return $node;
    }
}
