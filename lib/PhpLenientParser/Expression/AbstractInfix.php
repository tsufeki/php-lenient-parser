<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

abstract class AbstractInfix implements InfixInterface
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
     * @var int
     */
    private $associativity;

    public function __construct(int $token, int $precedence, int $associativity)
    {
        $this->token = $token;
        $this->precedence = $precedence;
        $this->associativity = $associativity;
    }

    public function getToken(): int
    {
        return $this->token;
    }

    public function getPrecedence(): int
    {
        return $this->precedence;
    }

    public function getAssociativity(): int
    {
        return $this->associativity;
    }
}
