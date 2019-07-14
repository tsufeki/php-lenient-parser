<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class DoubleCast extends Prefix
{
    public function __construct(int $precedence)
    {
        parent::__construct(Tokens::T_DOUBLE_CAST, $precedence, Node\Expr\Cast\Double::class);
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->lookAhead();
        $node = parent::parse($parser);
        if ($node !== null) {
            $cast = strtolower($token->value);
            $kind = Node\Expr\Cast\Double::KIND_DOUBLE;
            if (strpos($cast, 'float') !== false) {
                $kind = Node\Expr\Cast\Double::KIND_FLOAT;
            } elseif (strpos($cast, 'real') !== false) {
                $kind = Node\Expr\Cast\Double::KIND_REAL;
            }

            $node->setAttribute('kind', $kind);
        }

        return $node;
    }
}
