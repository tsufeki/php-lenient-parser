<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

abstract class AbstractOperator
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var int
     */
    private $precedence;

    /**
     * @var string
     */
    private $nodeClass;

    public function __construct(int $token, int $precedence, string $nodeClass)
    {
        $this->token = $token;
        $this->precedence = $precedence;
        $this->nodeClass = $nodeClass;
    }

    public function getToken(): int
    {
        return $this->token;
    }

    public function getPrecedence(): int
    {
        return $this->precedence;
    }

    public function getNodeClass(): string
    {
        if ($this->nodeClass === '') {
            throw new \LogicException();
        }

        return $this->nodeClass;
    }
}
