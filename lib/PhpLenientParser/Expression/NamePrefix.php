<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class NamePrefix extends AbstractPrefix
{
    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var FunctionCall
     */
    private $functionCallParser;

    /**
     * @var Scope
     */
    private $scopeParser;

    public function __construct(
        int $token,
        Name $nameParser,
        FunctionCall $functionCallParser,
        Scope $scopeParser
    ) {
        parent::__construct($token);
        $this->nameParser = $nameParser;
        $this->functionCallParser = $functionCallParser;
        $this->scopeParser = $scopeParser;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        if ($parser->lookAhead()->type === Tokens::T_STATIC && $parser->lookAhead(1)->type !== Tokens::T_PAAMAYIM_NEKUDOTAYIM) {
            return null;
        }

        $name = $this->nameParser->parse($parser, Name::ANY);
        if ($name === null) {
            return null;
        }

        switch ($parser->lookAhead()->type) {
            case Tokens::T_PAAMAYIM_NEKUDOTAYIM:
                return $this->scopeParser->parse($parser, $name);
            case ord('('):
                return $this->functionCallParser->parse($parser, $name);
            default:
                $node = new Node\Expr\ConstFetch($name);
                $parser->setAttributes($node, $name, $name);

                return $node;
        }
    }
}
