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
    public function __construct($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}
