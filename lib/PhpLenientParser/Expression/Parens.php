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
    public function __construct($openToken, $closeToken)
    {
        $this->openToken = $openToken;
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser)
    {
        $open = $parser->eat();
        $expr = $parser->getExpressionParser()->parse($parser);
        $close = $parser->assert($this->closeToken);
        if ($expr === null) {
            $expr = $parser->getExpressionParser()->makeErrorNode($open);
        }
        if ($close === null) {
            $close = $expr;
        }

        $parser->setAttributes($expr, $open, $close);
        return $expr;
    }

    public function getToken()
    {
        return $this->openToken;
    }
}
