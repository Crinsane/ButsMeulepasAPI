<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\HandleInventoryRequest;

class InventoryController extends Controller
{
    /**
     * Store the new inventory item.
     *
     * @param \Illuminate\Http\Request  $request
     * @return int
     */
    public function store(Request $request)
    {
        $this->dispatch(new HandleInventoryRequest($request));

        return 1;
    }
}
