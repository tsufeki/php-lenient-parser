<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;

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

    /**
     * @param int $token
     * @param Identifier $identifierParser
     * @param Name $nameParser
     * @param StatementParserInterface $classStatementsParser
     */
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
        $flags |= $parser->eat(Tokens::T_ABSTRACT) !== null ? Node\Stmt\Class_::MODIFIER_ABSTRACT : 0;
        $flags |= $parser->eat(Tokens::T_FINAL) !== null ? Node\Stmt\Class_::MODIFIER_FINAL : 0;
        $parser->eat();
        $id = $this->identifierParser->parse($parser);

        $extends = null;
        if ($parser->eat(Tokens::T_EXTENDS) !== null) {
            $extends = $this->nameParser->parserOrNull($parser);
        }

        $implements = [];
        if ($parser->eat(Tokens::T_IMPLEMENTS) !== null) {
            do {
                $impl = $this->nameParser->parserOrNull($parser);
                if ($impl !== null) {
                    $implements[] = $impl;
                }
            } while ($impl !== null && $parser->eat(ord(',')) !== null);
        }

        $stmts = [];
        if ($parser->assert(ord('{')) !== null) {
            $stmts = $this->classStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        return $parser->setAttributes(
            new Node\Stmt\Class_($id, [
                'flags' => $flags,
                'extends' => $extends,
                'implements' => $implements,
                'stmts' => $stmts,
            ]),
            $token, $parser->last()
        );
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return bool
     */
    private function isClass(ParserStateInterface $parser): bool
    {
        $i = 0;
        if (in_array($parser->lookAhead($i)->type, [Tokens::T_ABSTRACT, Tokens::T_FINAL])) {
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

    public function getToken()
    {
        return $this->token;
    }
}
