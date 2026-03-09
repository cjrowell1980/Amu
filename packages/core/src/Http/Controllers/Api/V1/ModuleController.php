<?php

namespace Amu\Core\Http\Controllers\Api\V1;

use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Http\Resources\GameModuleResource;
use Illuminate\Routing\Controller;

class ModuleController extends Controller
{
    public function index(ModuleRegistry $registry)
    {
        return GameModuleResource::collection($registry->all());
    }
}
