<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;
use PhpLenientParser\ParserStateInterface;

class InstanceOf_ extends AbstractOperator implements InfixInterface
{
    /**
     * @var ExpressionParserInterface
     */
    private $classRefParser;

    /**
     * @param int $token
     * @param int $precedence
     * @param ExpressionParserInterface $classRefParser
     */
    public function __construct($token, $precedence, $classRefParser)
    {
        parent::__construct($token, $precedence, Node\Expr\Instanceof_::class);
        $this->classRefParser = $classRefParser;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $right = $this->classRefParser->parse($parser);
        if ($right === null) {
            $right = $this->classRefParser->makeErrorNode($parser->last());
        }

        $class = $this->getNodeClass();
        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
