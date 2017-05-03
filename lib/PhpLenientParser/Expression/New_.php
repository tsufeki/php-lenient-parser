<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Statement\Class_;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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
     * @var Class_
     */
    private $classParser;

    /**
     * @param int                       $token
     * @param ExpressionParserInterface $classRefParser
     * @param ArgumentList              $argsParser
     * @param Class_                    $classParser
     */
    public function __construct(
        int $token,
        ExpressionParserInterface $classRefParser,
        ArgumentList $argsParser,
        Class_ $classParser
    ) {
        parent::__construct($token);
        $this->classRefParser = $classRefParser;
        $this->argsParser = $argsParser;
        $this->classParser = $classParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        // Check whether it's anonymous class:
        $classToken = $parser->eat(Tokens::T_CLASS);
        $class = $classToken === null ? $this->classRefParser->parseOrError($parser) : null;

        $args = [];
        if ($parser->lookAhead()->type === ord('(')) {
            $args = $this->argsParser->parse($parser);
        }

        if ($classToken !== null) {
            $class = $this->classParser->parseBody($parser, $classToken);
        }

        return $parser->setAttributes(new Node\Expr\New_($class, $args), $token, $parser->last());
    }
}
