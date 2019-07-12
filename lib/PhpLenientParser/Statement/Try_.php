<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Name;
use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

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
        while ($parser->isNext(Tokens::T_CATCH)) {
            $catch = $this->parseCatch($parser);
            if ($catch !== null) {
                $catches[] = $catch;
            }
        }

        $finally = null;
        if (null !== ($first = $parser->eatIf(Tokens::T_FINALLY))) {
            $finally = new Node\Stmt\Finally_($this->parseBlock($parser));
            $parser->setAttributes($finally, $first, $parser->last());
        }

        $node = new Node\Stmt\TryCatch($stmts, $catches, $finally);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    /**
     * @return Node\Stmt[]
     */
    private function parseBlock(ParserStateInterface $parser): array
    {
        $stmts = [];
        if ($parser->isNext(ord('{'))) {
            $stmts = $parser->getStatementParser()->parse($parser) ?: [];
        }

        return $stmts;
    }

    private function parseCatch(ParserStateInterface $parser): ?Node\Stmt\Catch_
    {
        $token = $parser->eat();
        if (!$parser->assert(ord('('))) {
            return null;
        }

        $types = [];
        do {
            $type = $this->nameParser->parse($parser);
            if ($type) {
                $types[] = $type;
            } else {
                break;
            }
        } while ($parser->eatIf(ord('|')) !== null);

        $var = null;
        if ($parser->isNext($this->variableParser->getToken())) {
            $var = $this->variableParser->parse($parser);
            assert($var instanceof Node\Expr\Variable);
        } else {
            $errorNode = $parser->getExpressionParser()->makeErrorNode($parser->last());
            $var = new Node\Expr\Variable($errorNode);
            $parser->setAttributes($var, $errorNode, $errorNode);
        }

        $parser->assert(ord(')'));
        $stmts = $this->parseBlock($parser);
        $node = new Node\Stmt\Catch_($types, $var, $stmts);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_TRY;
    }
}
