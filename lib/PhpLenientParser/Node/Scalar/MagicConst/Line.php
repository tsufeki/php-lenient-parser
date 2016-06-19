<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Line extends MagicConst
{
    public function getName() {
        return '__LINE__';
    }
}