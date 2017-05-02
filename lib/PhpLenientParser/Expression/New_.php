<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class New_ extends AbstractPrefix
{
    /**
     * @var ExpressionParserInterface
     */
    private $classRefParser;

    /**
     * @var ArgumentList
     */
    private $argsParser;

    /**
     * @param int                       $token
     * @param ExpressionParserInterface $classRefParser
     * @param ArgumentList              $argsParser
     */
    public function __construct(int $token, ExpressionParserInterface $classRefParser, ArgumentList $argsParser)
    {
        parent::__construct($token);
        $this->classRefParser = $classRefParser;
        $this->argsParser = $argsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        //TODO: anonymous class
        $token = $parser->eat();
        $class = $this->classRefParser->parseOrError($parser);

        $args = [];
        if ($parser->lookAhead()->type === ord('(')) {
            $args = $this->argsParser->parse($parser);
        }

        return $parser->setAttributes(new Node\Expr\New_($class, $args), $token, $parser->last());
    }
}
