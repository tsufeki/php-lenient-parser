<?php

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

    /**
     * @param Variable $variableParser
     * @param IndirectVariable $indirectVariableParser
     */
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
            if ($parser->lookAhead()->type === $this->variableParser->getToken()) {
                $var = $this->variableParser->parse($parser);
            } elseif ($parser->lookAhead()->type === $this->indirectVariableParser->getToken()) {
                $var = $this->indirectVariableParser->parse($parser);
            } else {
                break;
            }

            $vars[] = $var;
            if ($parser->lookAhead()->type === ord(';') || $parser->assert(ord(',')) === null) {
                break;
            }
        }

        $parser->assert(ord(';'));

        return $parser->setAttributes(new Node\Stmt\Global_($vars), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_GLOBAL;
    }
}
