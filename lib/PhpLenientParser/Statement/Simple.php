<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class Simple implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var string
     */
    private $nodeClass;

    /**
     * @var bool
     */
    private $expressionRequired;

    public function __construct(int $token, string $nodeClass, bool $expressionRequired = false)
    {
        $this->token = $token;
        $this->nodeClass = $nodeClass;
        $this->expressionRequired = $expressionRequired;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $expr = $parser->getExpressionParser()->parse($parser);
        if ($expr === null && $this->expressionRequired) {
            $expr = $parser->getExpressionParser()->makeErrorNode($parser->last());
        }
        $parser->assert(ord(';'));

        /** @var Node\Stmt */
        $node = new $this->nodeClass($expr, $parser->getAttributes($token, $parser->last()));

        return $node;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }
}
