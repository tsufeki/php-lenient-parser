<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

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

    public function __construct(int $openToken, int $closeToken)
    {
        $this->openToken = $openToken;
        $this->closeToken = $closeToken;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $open = $parser->eat();
        $expr = $parser->getExpressionParser()->parseOrError($parser);
        $parser->assert($this->closeToken);

        return $expr;
    }

    public function getToken(): int
    {
        return $this->openToken;
    }
}
