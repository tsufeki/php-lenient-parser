<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class InstanceOf_ extends AbstractOperator implements InfixInterface
{
    /**
     * @var ExpressionParserInterface
     */
    private $classRefParser;

    /**
     * @param int                       $token
     * @param int                       $precedence
     * @param ExpressionParserInterface $classRefParser
     */
    public function __construct(int $token, int $precedence, ExpressionParserInterface $classRefParser)
    {
        parent::__construct($token, $precedence, Node\Expr\Instanceof_::class);
        $this->classRefParser = $classRefParser;
    }

    public function parse(ParserStateInterface $parser, Node $left)
    {
        $token = $parser->eat();
        $right = $this->classRefParser->parseOrError($parser);

        $class = $this->getNodeClass();

        return $parser->setAttributes(new $class($left, $right), $left, $right);
    }
}
