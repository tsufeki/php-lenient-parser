<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Function_ extends MagicConst
{
    public function getName() {
        return '__FUNCTION__';
    }
}