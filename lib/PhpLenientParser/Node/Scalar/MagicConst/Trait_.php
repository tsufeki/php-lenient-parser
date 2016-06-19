<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Trait_ extends MagicConst
{
    public function getName() {
        return '__TRAIT__';
    }
}