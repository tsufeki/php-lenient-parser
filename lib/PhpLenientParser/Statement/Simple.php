<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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

    /**
     * @param int $token
     * @param string $nodeClass
     * @param bool $expressionRequired
     */
    public function __construct($token, $nodeClass, $expressionRequired = false)
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

        $nodeClass = $this->nodeClass;
        return $parser->setAttributes(new $nodeClass($expr), $token, $parser->last());
    }

    public function getToken()
    {
        return $this->token;
    }
}
