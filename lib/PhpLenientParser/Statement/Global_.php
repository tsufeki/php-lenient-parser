<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\IndirectVariable;
use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Global_ implements StatementInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    /**
     * @var IndirectVariable
     */
    private $indirectVariableParser;

    public function __construct(Variable $variableParser, IndirectVariable $indirectVariableParser)
    {
        $this->variableParser = $variableParser;
        $this->indirectVariableParser = $indirectVariableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $vars = [];

        while (true) {
            if ($parser->isNext($this->variableParser->getToken())) {
                $var = $this->variableParser->parse($parser);
                assert($var instanceof Node\Expr\Variable);
            } elseif ($parser->isNext($this->indirectVariableParser->getToken())) {
                $var = $this->indirectVariableParser->parse($parser);
                assert($var instanceof Node\Expr\Variable);
            } else {
                break;
            }

            $vars[] = $var;
            if ($parser->isNext(ord(';')) || !$parser->assert(ord(','))) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return new Node\Stmt\Global_($vars, $parser->getAttributes($token, $parser->last()));
    }

    public function getToken(): ?int
    {
        return Tokens::T_GLOBAL;
    }
}
