<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Class_ implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var Identifier
     */
    private $identifierParser;

    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var StatementParserInterface
     */
    private $classStatementsParser;

    public function __construct(
        int $token,
        Identifier $identifierParser,
        Name $nameParser,
        StatementParserInterface $classStatementsParser
    ) {
        $this->token = $token;
        $this->identifierParser = $identifierParser;
        $this->nameParser = $nameParser;
        $this->classStatementsParser = $classStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if (!$this->isClass($parser)) {
            return null;
        }

        $token = $parser->lookAhead();
        $flags = 0;
        while (in_array($parser->lookAhead()->type, [Tokens::T_ABSTRACT, Tokens::T_FINAL], true)) {
            if ($flags !== 0) {
                $parser->unexpected($parser->lookAhead(), Tokens::T_CLASS);
            }

            if ($parser->eatIf(Tokens::T_ABSTRACT) !== null) {
                $flags = Node\Stmt\Class_::MODIFIER_ABSTRACT;
            } elseif ($parser->eatIf(Tokens::T_FINAL) !== null) {
                $flags = Node\Stmt\Class_::MODIFIER_FINAL;
            }
        }

        $parser->eat();
        $id = $this->identifierParser->parse($parser);

        return $this->parseBody($parser, $token, $flags, $id);
    }

    public function parseBody(
        ParserStateInterface $parser,
        Token $token,
        int $flags = 0,
        ?Node\Identifier $id = null
    ): Node\Stmt\Class_ {
        $extends = null;
        if ($parser->eatIf(Tokens::T_EXTENDS) !== null) {
            $extends = $this->nameParser->parse($parser);
        }

        $implements = [];
        if ($parser->eatIf(Tokens::T_IMPLEMENTS) !== null) {
            do {
                $impl = $this->nameParser->parse($parser);
                if ($impl !== null) {
                    $implements[] = $impl;
                }
            } while ($impl !== null && $parser->eatIf(ord(',')) !== null);
        }

        $stmts = [];
        if ($parser->assert(ord('{'))) {
            $stmts = $this->classStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        return new Node\Stmt\Class_($id, [
            'flags' => $flags,
            'extends' => $extends,
            'implements' => $implements,
            'stmts' => $stmts,
        ], $parser->getAttributes($token, $parser->last()));
    }

    private function isClass(ParserStateInterface $parser): bool
    {
        $i = 0;
        while (in_array($parser->lookAhead($i)->type, [Tokens::T_ABSTRACT, Tokens::T_FINAL], true)) {
            $i++;
        }
        if ($parser->lookAhead($i)->type !== Tokens::T_CLASS) {
            return false;
        }
        $i++;
        if ($parser->lookAhead($i)->type !== Tokens::T_STRING) {
            return false;
        }

        return true;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }
}
