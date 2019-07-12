<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

interface StatementInterface
{
    /**
     * @return Node\Stmt|Node\Stmt[]|null
     */
    public function parse(ParserStateInterface $parser);

    public function getToken(): ?int;
}
