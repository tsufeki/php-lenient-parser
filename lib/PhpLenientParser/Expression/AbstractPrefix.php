<?php

namespace PhpLenientParser\Expression;

use PhpParser\Node;

abstract class AbstractPrefix implements PrefixInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @param int $token
     */
    public function __construct(int $token)
    {
        $this->token = $token;
    }

    public function getToken(): int
    {
        return $this->token;
    }
}
