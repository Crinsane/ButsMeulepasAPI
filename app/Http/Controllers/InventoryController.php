<?php

namespace App\Http\Controllers;

use App\Handlers\PostRequest;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use App\Extractors\XmlExtractor;
use Intervention\Image\Facades\Image;

class InventoryController extends Controller
{
//    /**
//     * Store the new inventory item.
//     *
//     * @param \Illuminate\Http\Request     $request
//     * @param \App\Extractors\XmlExtractor $extractor
//     * @return int
//     */
//    public function store(Request $request, XmlExtractor $extractor)
//    {
//        $extractor->process($request->getContent());
//
//        return 1;
//    }

    /**
     * Store the new inventory item.
     *
     * @param \Illuminate\Http\Request     $request
     * @return int
     */
    public function store(Request $request, PostRequest $postRequest)
    {
        dd($postRequest->handle($request));

        $images = collect(explode(',', $request->afbeeldingen));

        $images->each(function ($image) use ($client) {
            if (empty($image)) return;

            $basename = str_replace('?do_not_embed', '', basename($image));

            Image::make($image)->save($imagePath = storage_path('app/'.$basename));

            $file = new \SplFileObject($imagePath, "r");

            $imageData = $file->fread($file->getSize());

            $imageResponse = $client->request('POST', 'http://buts-wp.dev/wp-json/wp/v2/media', [
                'headers' => [
                    'Authorization' => 'Basic Um9iOkZvcm11bGUx',
                    'Content-Disposition' => 'form-data; name="file"; filename="'.$basename.'"',
                    'Content-Length' => $file->getSize(),
                ],
                'body' => $imageData,
            ]);

            dd($imageResponse->getStatusCode(), json_decode($imageResponse->getBody()->getContents()));
        });

        $response = $client->request('POST', 'http://buts-wp.dev/wp-json/wp/v2/posts', [
            'headers' => ['Authorization' => 'Basic Um9iOkZvcm11bGUx'],
            'form_params' => [
                'title' => $request->merk . ' ' . $request->type,
                'slug' => $request->voertuignr,
                'content' => $request->opmerkingen,
                'status' => 'publish'
            ]
        ]);

        dd($response->getStatusCode(), $response->getBody());

        return 1;
    }
}
