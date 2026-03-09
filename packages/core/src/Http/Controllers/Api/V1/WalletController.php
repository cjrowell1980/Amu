<?php

namespace Amu\Core\Http\Controllers\Api\V1;

use Amu\Core\Contracts\WalletManager;
use Amu\Core\Http\Resources\WalletAccountResource;
use Illuminate\Routing\Controller;

class WalletController extends Controller
{
    public function show(WalletManager $wallets): WalletAccountResource
    {
        $account = $wallets->accountFor(request()->user(), request('type', 'credits'));

        return new WalletAccountResource($account);
    }
}
