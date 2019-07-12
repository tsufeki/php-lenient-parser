<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

abstract class AbstractPrefix implements PrefixInterface
{
    /**
     * @var int
     */
    private $token;

    public function __construct(int $token)
    {
        $this->token = $token;
    }

    public function getToken(): int
    {
        return $this->token;
    }
}
