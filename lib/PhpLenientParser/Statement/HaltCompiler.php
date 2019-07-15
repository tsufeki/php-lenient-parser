<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class HaltCompiler implements StatementInterface
{
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $rest = $token->startAttributes['rest'] ?? '';
        unset($token->startAttributes['rest']);

        return new Node\Stmt\HaltCompiler($rest, $parser->getAttributes($token, $token));
    }

    public function getToken(): ?int
    {
        return Tokens::T_HALT_COMPILER;
    }
}
