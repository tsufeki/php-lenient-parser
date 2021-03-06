<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;

class AggregateStatementParser extends AbstractStatementParser
{
    /**
     * @var StatementParserInterface[]
     */
    private $parsers;

    public function __construct(StatementParserInterface ...$parsers)
    {
        $this->parsers = $parsers;
    }

    public function parse(ParserStateInterface $state): ?array
    {
        foreach ($this->parsers as $parser) {
            $stmt = $parser->parse($state);
            if ($stmt !== null) {
                return $stmt;
            }
        }

        return null;
    }
}
