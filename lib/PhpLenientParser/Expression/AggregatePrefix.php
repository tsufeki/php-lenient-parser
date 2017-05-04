<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;

class AggregatePrefix extends AbstractPrefix
{
    /**
     * @var PrefixInterface[]
     */
    private $prefixes;

    /**
     * @param PrefixInterface[] $prefixes
     */
    public function __construct(PrefixInterface ...$prefixes)
    {
        parent::__construct($prefixes[0]->getToken());
        $this->prefixes = $prefixes;
    }

    public function parse(ParserStateInterface $parser)
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
