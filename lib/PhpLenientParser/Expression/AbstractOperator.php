<?php

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

    /**
     * @param int $token
     * @param int $precedence
     * @param string $nodeClass
     */
    public function __construct($token, $precedence, $nodeClass)
    {
        $this->token = $token;
        $this->precedence = $precedence;
        $this->nodeClass = $nodeClass;
    }

    /**
     * @return int
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * @return string
     */
    public function getNodeClass()
    {
        return $this->nodeClass;
    }
}
