<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Dir extends MagicConst
{
    public function getName() {
        return '__DIR__';
    }
}