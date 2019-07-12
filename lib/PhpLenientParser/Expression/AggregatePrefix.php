<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class AggregatePrefix extends AbstractPrefix
{
    /**
     * @var PrefixInterface[]
     */
    private $prefixes;

    public function __construct(PrefixInterface ...$prefixes)
    {
        parent::__construct($prefixes[0]->getToken());
        $this->prefixes = $prefixes;
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        foreach ($this->prefixes as $prefix) {
            $node = $prefix->parse($parser);
            if ($node !== null) {
                return $node;
            }
        }

        return null;
    }
}
