<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\Expression\Identifier;

class Type
{
    const BUILTIN_TYPES = [
        'bool'     => true,
        'int'      => true,
        'float'    => true,
        'string'   => true,
        'iterable' => true,
        'void'     => true,
    ];

    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @param Name $nameParser
     * @param Identifier $identifierParser
     */
    public function __construct(Name $nameParser, Identifier $identifierParser)
    {
        $this->nameParser = $nameParser;
        $this->identifierParser = $identifierParser;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Name|Node\Identifier|Node\NullableType|null
     */
    public function parse(ParserStateInterface $parser)
    {
        /** @var Node\Name|Node\Identifier|Node\NullableType|null $type */
        $type = null;
        $nullable = $parser->eat(ord('?'));
        switch ($parser->lookAhead()->type) {
            case Tokens::T_STRING:
            case Tokens::T_NS_SEPARATOR:
                $type = $this->nameParser->parse($parser);

                if ($type->isUnqualified() && isset(static::BUILTIN_TYPES[strtolower($type->toString())])) {
                    $type = new Node\Identifier(strtolower($type->toString()), $type->getAttributes());
                }
                break;
            case Tokens::T_ARRAY:
            case Tokens::T_CALLABLE:
                $type = $this->identifierParser->parse($parser);
                break;
        }
        if ($type !== null && $nullable !== null) {
            $type = $parser->setAttributes(new Node\NullableType($type), $nullable, $parser->last());
        }

        return $type;
    }
}
