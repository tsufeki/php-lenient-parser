<?php

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;
use PhpLenientParser\Expression\Name;

class Try_ implements StatementInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @param Variable $variableParser
     * @param Name $nameParser
     */
    public function __construct(Variable $variableParser, Name $nameParser)
    {
        $this->variableParser = $variableParser;
        $this->nameParser = $nameParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $stmts = $this->parseBlock($parser);

        $catches = [];
        while ($parser->lookAhead()->type === Tokens::T_CATCH) {
            $catch = $this->parseCatch($parser);
            if ($catch !== null) {
                $catches[] = $catch;
            }
        }

        $finally = null;
        if (null !== ($first = $parser->eat(Tokens::T_FINALLY))) {
            $finally = $parser->setAttributes(
                new Node\Stmt\Finally_($this->parseBlock($parser)),
                $first, $parser->last()
            );
        }

        return $parser->setAttributes(new Node\Stmt\TryCatch(
            $stmts,
            $catches,
            $finally
        ), $token, $parser->last());
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt[]
     */
    private function parseBlock(ParserStateInterface $parser): array
    {
        $stmts = [];
        if ($parser->lookAhead()->type === ord('{')) {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return $stmts;
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node\Stmt\Catch_|null
     */
    private function parseCatch(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        if ($parser->assert(ord('(')) === null) {
            return null;
        }

        $types = [];
        do {
            if (in_array($parser->lookAhead()->type, [Tokens::T_STRING, Tokens::T_NS_SEPARATOR])) {
                $types[] = $this->nameParser->parse($parser);
            } else {
                break;
            }
        } while ($parser->eat(ord('|')) !== null);

        $var = null;
        if ($parser->lookAhead()->type === $this->variableParser->getToken()) {
            $var = $this->variableParser->parse($parser);
        } else {
            $errorNode = $parser->getExpressionParser()->makeErrorNode($parser->last());
            $var = $parser->setAttributes(new Node\Expr\Variable($errorNode), $errorNode, $errorNode);
        }

        $parser->assert(ord(')'));
        $stmts = $this->parseBlock($parser);

        return $parser->setAttributes(new Node\Stmt\Catch_($types, $var, $stmts), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_TRY;
    }
}
