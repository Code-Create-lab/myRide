<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;

class SosController extends Controller
{
    public function sos()
    {
        $pageTitle = 'All  SOS';
        $soss   = SosAlert::with(['ride', 'ride.user', 'ride.driver'])
            ->searchable(['ride:uid', 'user:username', 'driver:username'])
            ->orderBy("id", getOrderBy())
            ->paginate(getPaginate());
        return view('admin.sos.all', compact('pageTitle', 'soss'));
    }
}
