<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Extractors\XmlExtractor;

class InventoryController extends Controller
{
    /**
     * Store the new inventory item.
     * 
     * @param \Illuminate\Http\Request     $request
     * @param \App\Extractors\XmlExtractor $extractor
     * @return int
     */
    public function store(Request $request, XmlExtractor $extractor)
    {
        $extractor->process($request->getContent());

        return 1;
    }
}
