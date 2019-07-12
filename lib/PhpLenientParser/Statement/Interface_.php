<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Interface_ implements StatementInterface
{
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
        Identifier $identifierParser,
        Name $nameParser,
        StatementParserInterface $classStatementsParser
    ) {
        $this->identifierParser = $identifierParser;
        $this->nameParser = $nameParser;
        $this->classStatementsParser = $classStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== Tokens::T_STRING) {
            return null;
        }

        $token = $parser->eat();
        $id = $this->identifierParser->parse($parser);
        assert($id !== null);

        $extends = [];
        if ($parser->eatIf(Tokens::T_EXTENDS) !== null) {
            do {
                $ext = $this->nameParser->parse($parser);
                if ($ext !== null) {
                    $extends[] = $ext;
                }
            } while ($ext !== null && $parser->eatIf(ord(',')) !== null);
        }

        $stmts = [];
        if ($parser->assert(ord('{'))) {
            $stmts = $this->classStatementsParser->parseList($parser, ord('}'));
            $parser->assert(ord('}'));
        }

        $node = new Node\Stmt\Interface_($id, ['extends' => $extends, 'stmts' => $stmts]);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_INTERFACE;
    }
}
