<?php

namespace Amu\Core\Http\Controllers\Admin;

use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Models\GameModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ModuleController extends Controller
{
    public function index(ModuleRegistry $registry)
    {
        return view('core::admin.modules.index', ['modules' => $registry->all()]);
    }

    public function toggle(GameModule $module, ModuleRegistry $registry): RedirectResponse
    {
        $module->enabled ? $registry->disable($module) : $registry->enable($module);

        return back();
    }
}
