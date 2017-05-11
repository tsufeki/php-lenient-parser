<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;

class Parens implements PrefixInterface
{
    /**
     * @var int
     */
    private $openToken;

    /**
     * @var int
     */
    private $closeToken;

    /**
     * @param int $openToken
     * @param int $closeToken
     */
    public function __construct(int $openToken, int $closeToken)
    {
        $this->openToken = $openToken;
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser)
    {
        $open = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert($this->closeToken);

        $parser->setAttributes($expr, $open, $parser->last());

        return $expr;
    }

    public function getToken(): int
    {
        return $this->openToken;
    }
}
