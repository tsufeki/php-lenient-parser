<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class InstanceOf_ extends AbstractInfix
{
    /**
     * @var ClassNameReference
     */
    private $classRefParser;

    public function __construct(int $token, int $precedence, ClassNameReference $classRefParser)
    {
        parent::__construct($token, $precedence, self::NOT_ASSOCIATIVE);
        $this->classRefParser = $classRefParser;
    }

    public function parse(ParserStateInterface $parser, Node\Expr $left): ?Node\Expr
    {
        $token = $parser->eat();
        $right = $this->classRefParser->parseOrError($parser);

        $node = new Node\Expr\Instanceof_($left, $right);
        $parser->setAttributes($node, $left, $right);

        return $node;
    }
}
