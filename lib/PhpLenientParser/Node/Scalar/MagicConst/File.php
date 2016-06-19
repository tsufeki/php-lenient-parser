<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class File extends MagicConst
{
    public function getName() {
        return '__FILE__';
    }
}