<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Handlers\PostRequest;

class InventoryController extends Controller
{
    /**
     * Store the new inventory item.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Handlers\PostRequest $postRequest
     * @return int
     */
    public function store(Request $request, PostRequest $postRequest)
    {
        $postRequest->handle($request);

        return 1;
    }
}
