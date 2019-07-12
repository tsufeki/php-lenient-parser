<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Statement\Class_;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class New_ extends AbstractPrefix
{
    /**
     * @var ClassNameReference
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

    public function __construct(
        int $token,
        ClassNameReference $classRefParser,
        ArgumentList $argsParser,
        Class_ $classParser
    ) {
        parent::__construct($token);
        $this->classRefParser = $classRefParser;
        $this->argsParser = $argsParser;
        $this->classParser = $classParser;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->eat();
        // Check whether it's anonymous class:
        $classToken = $parser->eatIf(Tokens::T_CLASS);
        $class = null;
        if ($classToken === null) {
            $class = $this->classRefParser->parseOrError($parser);
        }

        $args = [];
        if ($parser->isNext(ord('('))) {
            $args = $this->argsParser->parse($parser);
        }

        if ($class === null) {
            assert($classToken !== null);
            $class = $this->classParser->parseBody($parser, $classToken);
        }

        $node = new Node\Expr\New_($class, $args);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }
}
