<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Variable;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Static_ implements StatementInterface
{
    /**
     * @var Variable
     */
    private $variableParser;

    public function __construct(Variable $variableParser)
    {
        $this->variableParser = $variableParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        if ($parser->lookAhead(1)->type !== $this->variableParser->getToken()) {
            return null;
        }

        $token = $parser->eat();
        $vars = [];

        while ($parser->isNext($this->variableParser->getToken())) {
            $var = $this->variableParser->parse($parser);
            assert($var instanceof Node\Expr\Variable);
            $expr = null;
            if ($parser->eatIf(ord('=')) !== null) {
                $expr = $parser->getExpressionParser()->parseOrError($parser);
            }

            $svar = new Node\Stmt\StaticVar($var, $expr);
            $parser->setAttributes($svar, $var, $parser->last());
            $vars[] = $svar;

            if ($parser->isNext(ord(';')) || !$parser->assert(ord(','))) {
                break;
            }
        }

        $parser->assert(ord(';'));
        $node = new Node\Stmt\Static_($vars);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_STATIC;
    }
}
