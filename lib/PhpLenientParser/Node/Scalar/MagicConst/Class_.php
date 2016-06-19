<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Class_ extends MagicConst
{
    public function getName() {
        return '__CLASS__';
    }
}