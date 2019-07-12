<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class ClassNameReference
{
    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var ScopeNew
     */
    private $scopeParser;

    /**
     * @var ExpressionParser
     */
    private $expressionParser;

    public function __construct(
        Name $nameParser,
        ScopeNew $scopeParser
    ) {
        $this->nameParser = $nameParser;
        $this->scopeParser = $scopeParser;
        $this->expressionParser = new ExpressionParser();
    }

    /**
     * @return Node\Name|Node\Expr|null
     */
    public function parse(ParserStateInterface $parser): ?Node
    {
        $name = $this->nameParser->parse($parser, Name::ANY);
        if ($name !== null) {
            if (!$parser->isNext(Tokens::T_PAAMAYIM_NEKUDOTAYIM)) {
                return $name;
            }

            $left = $this->scopeParser->parse($parser, $name);

            return $this->expressionParser->parseInfix($parser, $left);
        }

        return $this->expressionParser->parse($parser);
    }

    /**
     * @return Node\Name|Node\Expr
     */
    public function parseOrError(ParserStateInterface $parser): Node
    {
        $expr = $this->parse($parser);
        if ($expr === null) {
            $expr = $this->expressionParser->makeErrorNode($parser->last());
        }

        return $expr;
    }

    public function addPrefix(PrefixInterface $prefix): void
    {
        $this->expressionParser->addPrefix($prefix);
    }

    public function addInfix(InfixInterface $infix): void
    {
        $this->expressionParser->addInfix($infix);
    }
}
