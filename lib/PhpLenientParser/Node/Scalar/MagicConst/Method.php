<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Method extends MagicConst
{
    public function getName() {
        return '__METHOD__';
    }
}