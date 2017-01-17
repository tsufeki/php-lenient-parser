<?php

namespace PhpLenientParser;

use PhpParser\Parser\Tokens;

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
     * @var string[]
     */
    private static $names = null;

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

    /**
     * @return string
     */
    public function getName()
    {
        if (self::$names === null) {
            self::loadNames();
        }

        if (isset(self::$names[$this->type])) {
            return self::$names[$this->type];
        }

        return $this->type === ord("'") ? "\\'" : "'" . chr($this->type) . "'";
    }

    private static function loadNames()
    {
        self::$names = array_flip((new \ReflectionClass(Tokens::class))->getConstants());
        self::$names[0] = 'EOF';
    }
}
