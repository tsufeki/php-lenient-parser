<?php

namespace PhpLenientParser;

final class Token
{
    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $value = '';

    /**
     * @var array
     */
    public $startAttributes = [];

    /**
     * @var array
     */
    public $endAttributes = [];

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->startAttributes + $this->endAttributes;
    }
}
