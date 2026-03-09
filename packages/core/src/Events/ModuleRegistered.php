<?php

namespace Amu\Core\Events;

use Amu\Core\Models\GameModule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public GameModule $module)
    {
    }
}
