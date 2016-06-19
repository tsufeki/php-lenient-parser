<?php

namespace PhpLenientParser\Node\Stmt;

use PhpLenientParser\Node;

/** Nop/empty statement (;). */
class Nop extends Node\Stmt
{
    public function getSubNodeNames() {
        return array();
    }
}
