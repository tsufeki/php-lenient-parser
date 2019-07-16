<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;

class TopLevel extends AggregateStatementParser
{
    public function parseList(ParserStateInterface $parser, int ...$delimiters): array
    {
        $stmts = parent::parseList($parser, ...$delimiters);

        $namespaceKind = null;
        $beforeNamespaceStmt = null;
        $betweenNamespaceStmt = null;
        $mixedNamespaces = false;

        foreach ($stmts ?? [] as $i => $stmt) {
            if ($stmt instanceof Node\Stmt\Namespace_) {
                if ($namespaceKind === null) {
                    $namespaceKind = $stmt->getAttribute('kind');
                    if ($beforeNamespaceStmt !== null) {
                        $parser->addError(
                            'Namespace declaration statement has to be the very first statement in the script',
                            ['startLine' => $stmt->getLine()]
                        );
                    }
                } elseif ($namespaceKind !== $stmt->getAttribute('kind')) {
                    $mixedNamespaces = true;
                    $parser->addError(
                            'Cannot mix bracketed namespace declarations with unbracketed namespace declarations',
                            ['startLine' => $stmt->getLine()]
                        );
                }
                continue;
            }

            if ($namespaceKind === null && (
                $stmt instanceof Node\Stmt\Declare_
                || ($i === 0 && $stmt instanceof Node\Stmt\InlineHTML && preg_match('/\\A#!.*(\\n|\\r\\n|\\r)\\z/', $stmt->value))
            )) {
                continue;
            }

            if ($stmt instanceof Node\Stmt\Nop
                || $stmt instanceof Node\Stmt\HaltCompiler
            ) {
                continue;
            }

            if ($namespaceKind === null && $beforeNamespaceStmt === null) {
                $beforeNamespaceStmt = $stmt;
            } elseif ($namespaceKind !== null && $betweenNamespaceStmt === null) {
                $betweenNamespaceStmt = $stmt;
            }
        }

        if ($betweenNamespaceStmt !== null && !$mixedNamespaces) {
            $parser->addError('No code may exist outside of namespace {}', $betweenNamespaceStmt->getAttributes());
        }

        return $stmts;
    }
}
