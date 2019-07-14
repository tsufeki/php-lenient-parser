<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Type
{
    private const BUILTIN_TYPES = [
        'bool' => true,
        'int' => true,
        'float' => true,
        'string' => true,
        'iterable' => true,
        'void' => true,
        'object' => true,
    ];

    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    public function __construct(Name $nameParser, Identifier $identifierParser)
    {
        $this->nameParser = $nameParser;
        $this->identifierParser = $identifierParser;
    }

    /**
     * @return Node\Name|Node\Identifier|Node\NullableType|null
     */
    public function parse(ParserStateInterface $parser)
    {
        /** @var Node\Name|Node\Identifier|Node\NullableType|null $type */
        $type = null;
        $nullable = $parser->eatIf(ord('?'));

        $type = $this->nameParser->parse($parser);
        if ($type !== null && $type->isUnqualified() && isset(static::BUILTIN_TYPES[strtolower($type->toString())])) {
            $type = new Node\Identifier(strtolower($type->toString()), $type->getAttributes());
        }

        if ($type === null && $parser->isNext(Tokens::T_ARRAY, Tokens::T_CALLABLE)) {
            $type = $this->identifierParser->parse($parser);
            assert($type !== null);
            $type->name = strtolower($type->name);
        }

        if ($type !== null && $nullable !== null) {
            $type = new Node\NullableType($type);
            $parser->setAttributes($type, $nullable, $parser->last());
        }

        return $type;
    }
}
