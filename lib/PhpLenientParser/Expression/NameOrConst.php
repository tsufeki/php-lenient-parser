<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\Name as NameNode;
use PhpParser\Parser\Tokens;

class NameOrConst extends AbstractPrefix
{
    /**
     * @var Name
     */
    private $nameParser;

    public function __construct(int $token)
    {
        parent::__construct($token);
        $this->nameParser = new Name($token);
    }

    /**
     * @param ParserStateInterface $parser
     *
     * @return NameNode|Expr\ConstFetch|null
     */
    public function parse(ParserStateInterface $parser)
    {
        $name = $this->nameParser->parse($parser);
        if ($name === null) {
            return null;
        }

        switch ($parser->lookAhead()->type) {
            case Tokens::T_PAAMAYIM_NEKUDOTAYIM:
            case ord('('):
                return $name;
            default:
                return $parser->setAttributes(new Expr\ConstFetch($name), $name, $name);
        }
    }
}
