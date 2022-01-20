<?php

declare(strict_types=1);

namespace AppUtils;

interface Interface_ClassableViaAttributes extends Interface_Classable
{
    public function getAttributes() : AttributeCollection;
}
