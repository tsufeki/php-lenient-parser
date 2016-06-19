<?php

namespace PhpLenientParser\Node\ErrorNode;

use PhpLenientParser\NodeAbstract;

class Nothing extends NodeAbstract
{
    public function getSubNodeNames()
    {
        return array();
    }
}
