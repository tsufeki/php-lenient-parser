<?php

namespace PhpLenientParser\Node\Scalar\MagicConst;

use PhpLenientParser\Node\Scalar\MagicConst;

class Namespace_ extends MagicConst
{
    public function getName() {
        return '__NAMESPACE__';
    }
}