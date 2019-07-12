<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class MemberModifier implements StatementInterface
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var int
     */
    private $modifier;

    /**
     * @var StatementParserInterface
     */
    private $classStatementsParser;

    public function __construct(int $token, int $modifier, StatementParserInterface $classStatementsParser)
    {
        $this->token = $token;
        $this->modifier = $modifier;
        $this->classStatementsParser = $classStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        /** @var (Node\Stmt\ClassConst|Node\Stmt\ClassMethod|Node\Stmt\Property)[] */
        $stmts = $this->classStatementsParser->parse($parser);
        if ($stmts === []) {
            $parser->unexpected($token);

            return null;
        }
        if (!isset($stmts[0]->flags)) {
            $parser->unexpected($token);

            return $stmts[0];
        }

        $stmts[0]->flags |= $this->modifier;
        $parser->setAttributes($stmts[0], $token, $stmts[0]);

        return $stmts[0];
    }

    public function getToken(): ?int
    {
        return $this->token;
    }
}
