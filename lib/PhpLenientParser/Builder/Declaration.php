<?php

namespace PhpLenientParser\Builder;

use PhpLenientParser;
use PhpLenientParser\Node;
use PhpLenientParser\Node\Stmt;

abstract class Declaration extends PhpLenientParser\BuilderAbstract
{
    protected $attributes = array();

    abstract public function addStmt($stmt);

    /**
     * Adds multiple statements.
     *
     * @param array $stmts The statements to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmts(array $stmts) {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }

        return $this;
    }

    /**
     * Sets doc comment for the declaration.
     *
     * @param PhpLenientParser\Comment\Doc|string $docComment Doc comment to set
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDocComment($docComment) {
        $this->attributes['comments'] = array(
            $this->normalizeDocComment($docComment)
        );

        return $this;
    }
}