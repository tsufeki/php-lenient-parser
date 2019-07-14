<?php declare(strict_types=1);

namespace PhpLenientParser;

use PhpParser\Parser\Tokens;

final class Token
{
    /**
     * @var int
     */
    public $type = -1;

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
        return self::getNameFromType($this->type, $this->value);
    }

    public static function getNameFromType(int $type, ?string $value = null): string
    {
        self::loadNames();

        if (isset(self::$names[$type])) {
            return self::$names[$type];
        }

        throw new \RangeException("The lexer returned an invalid token (id=$type" . ($value !== null ? ", value=$value" : '') . ')');
    }

    private static function loadNames(): void
    {
        if (self::$names === null) {
            self::$names = array_flip((new \ReflectionClass(Tokens::class))->getConstants());

            $chars = '!"$%&()*+,-./:;<=>?@[]^`{|}~'; // No #'\_
            $charsLength = strlen($chars);
            for ($i = 0; $i < $charsLength; $i++) {
                self::$names[ord($chars[$i])] = "'{$chars[$i]}'";
            }

            self::$names[ord("'")] = "'\\''";
            self::$names[0] = 'EOF';
            self::$names[-1] = 'T_BAD_CHARACTER';
        }
    }
}
