<?php declare(strict_types=1);

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

    public function getName(): string
    {
        return self::getNameFromType($this->type);
    }

    public static function getNameFromType(int $type): string
    {
        self::loadNames();

        if (isset(self::$names[$type])) {
            return self::$names[$type];
        }

        return $type === ord("'") ? "'\\''" : "'" . chr($type) . "'";
    }

    private static function loadNames(): void
    {
        if (self::$names === null) {
            self::$names = array_flip((new \ReflectionClass(Tokens::class))->getConstants());
            self::$names[0] = 'EOF';
        }
    }
}
