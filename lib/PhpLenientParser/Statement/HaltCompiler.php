<?php

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

        return $parser->setAttributes(new Node\Stmt\HaltCompiler($rest), $token, $token);
    }

    public function getToken()
    {
        return Tokens::T_HALT_COMPILER;
    }
}
