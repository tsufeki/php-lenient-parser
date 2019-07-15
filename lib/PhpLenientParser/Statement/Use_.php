<?php declare(strict_types=1);

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
        if ($name === null || $parser->eatIf(ord('{')) === null) {
            while ($name !== null) {
                $alias = $this->parseAlias($parser);
                $uses[] = new Node\Stmt\UseUse($name, $alias, Node\Stmt\Use_::TYPE_UNKNOWN, $parser->getAttributes($name, $parser->last()));

                $name = null;
                if ($parser->eatIf(ord(',')) !== null && !$parser->isNext(ord(';'))) {
                    $name = $this->parseName($parser);
                }
            }

            $parser->assert(ord(';'));
            $node = new Node\Stmt\Use_($uses, $type, $parser->getAttributes($token, $parser->last()));
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
                $uses[] = new Node\Stmt\UseUse($name, $alias, $innerType, $parser->getAttributes($name, $parser->last()));

                if ($parser->eatIf(ord(',')) === null) {
                    break;
                }
            }

            $parser->assert(ord('}'));
            $parser->assert(ord(';'));
            $node = new Node\Stmt\GroupUse($prefix, $uses, $type, $parser->getAttributes($token, $parser->last()));
        }

        return $node;
    }

    private function parseType(ParserStateInterface $parser): int
    {
        $type = Node\Stmt\Use_::TYPE_NORMAL;
        if ($parser->eatIf(Tokens::T_CONST)) {
            $type = Node\Stmt\Use_::TYPE_CONSTANT;
        }
        if ($parser->eatIf(Tokens::T_FUNCTION)) {
            $type = Node\Stmt\Use_::TYPE_FUNCTION;
        }

        return $type;
    }

    private function parseName(ParserStateInterface $parser, bool $trailingSep = false): ?Node\Name
    {
        while ($parser->eatIf(Tokens::T_NS_SEPARATOR) !== null);

        return $this->nameParser->parse($parser, Name::NORMAL, $trailingSep);
    }

    private function parseAlias(ParserStateInterface $parser): ?Node\Identifier
    {
        $alias = null;
        if ($parser->eatIf(Tokens::T_AS) !== null && $parser->isNext(Tokens::T_STRING)) {
            $alias = $this->identifierParser->parse($parser);
        }

        return $alias;
    }

    public function getToken(): ?int
    {
        return Tokens::T_USE;
    }
}
