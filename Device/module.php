<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/DeconzBaseModule.php';
require_once __DIR__ . '/../libs/DeconzHelper.php';

class DeconzDevice extends IPSModule
{
    use DeconzBaseModule;
    use DeconzHelper;
}
