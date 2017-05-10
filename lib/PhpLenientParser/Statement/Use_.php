<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Use_ implements StatementInterface
{
    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @param Name       $nameParser
     * @param Identifier $identifierParser
     */
    public function __construct(Name $nameParser, Identifier $identifierParser)
    {
        $this->nameParser = $nameParser;
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $uses = [];
        $type = $this->parseType($parser);

        $name = $this->parseName($parser, true);
        if ($name === null || $parser->eat(ord('{')) === null) {
            while ($name !== null) {
                $alias = $this->parseAlias($parser);
                $uses[] = $parser->setAttributes(
                    new Node\Stmt\UseUse($name, $alias, Node\Stmt\Use_::TYPE_UNKNOWN),
                    $name, $parser->last()
                );

                $name = null;
                if ($parser->eat(ord(',')) !== null && $parser->lookAhead()->type !== ord(';')) {
                    $name = $this->parseName($parser);
                }
            }

            $node = new Node\Stmt\Use_($uses, $type);
        } else {
            $prefix = $name;
            $type = $type === Node\Stmt\Use_::TYPE_NORMAL ? Node\Stmt\Use_::TYPE_UNKNOWN : $type;
            while (true) {
                $innerType = $this->parseType($parser);
                if ($type !== Node\Stmt\Use_::TYPE_UNKNOWN) {
                    $innerType = Node\Stmt\Use_::TYPE_UNKNOWN;
                }
                $name = $this->parseName($parser);
                if ($name === null) {
                    break;
                }
                $alias = $this->parseAlias($parser);
                $uses[] = $parser->setAttributes(
                    new Node\Stmt\UseUse($name, $alias, $innerType),
                    $name, $parser->last()
                );
                if ($parser->eat(ord(',')) === null && $parser->lookAhead()->type !== ord('}')) {
                    break;
                }
            }

            $parser->assert(ord('}'));
            $node = new Node\Stmt\GroupUse($prefix, $uses, $type);
        }
        $parser->assert(ord(';'));

        return $parser->setAttributes($node, $token, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return int
     */
    private function parseType(ParserStateInterface $parser): int
    {
        $type = Node\Stmt\Use_::TYPE_NORMAL;
        if ($parser->eat(Tokens::T_CONST)) {
            $type = Node\Stmt\Use_::TYPE_CONSTANT;
        }
        if ($parser->eat(Tokens::T_FUNCTION)) {
            $type = Node\Stmt\Use_::TYPE_FUNCTION;
        }

        return $type;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Name|null
     */
    private function parseName(ParserStateInterface $parser, bool $trailingSep = false)
    {
        while ($parser->eat(Tokens::T_NS_SEPARATOR) !== null);

        return $this->nameParser->parserOrNull($parser, Name::NORMAL, $trailingSep);
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Identifier|string|null
     */
    private function parseAlias(ParserStateInterface $parser)
    {
        $alias = null;
        if ($parser->eat(Tokens::T_AS) !== null && $parser->lookAhead()->type === Tokens::T_STRING) {
            $alias = $this->identifierParser->parse($parser);
        }

        return $alias;
    }

    public function getToken()
    {
        return Tokens::T_USE;
    }
}
