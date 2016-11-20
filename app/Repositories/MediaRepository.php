<?php

namespace App\Repositories;

use Intervention\Image\Facades\Image;

class MediaRepository extends Repository
{
    /**
     * Create a new media.
     *
     * @param string $image
     * @return \stdClass
     */
    public function createMedia($image)
    {
        $basename = str_replace('?do_not_embed', '', basename($image));

        Image::make($image)->save($imagePath = storage_path("app/{$basename}"));

        $file = new \SplFileObject($imagePath, 'r');

        $imageData = $file->fread($file->getSize());

        $response = $this->client->request('POST', 'media', [
            'headers' => [
                'Content-Disposition' => "form-data; name=\"file\"; filename=\"{$basename}\"",
                'Content-Length' => $file->getSize(),
            ],
            'body' => $imageData,
        ]);

        @unlink($imagePath);

        return $this->decodeResponse($response);
    }

    /**
     * Attach the media to the post.
     *
     * @param int $mediaId
     * @param int $postId
     * @return void
     */
    public function attachToPost($mediaId, $postId)
    {
        $this->client->request('POST', 'media/'.$mediaId, [
            'form_params' => ['post' => $postId]
        ]);
    }

    /**
     * Get all media by the given url.
     *
     * @param string $url
     * @return array
     */
    public function getByUrl($url)
    {
        $response = $this->client->request('GET', $url);

        return $this->decodeResponse($response);
    }

    /**
     * Delete the media.
     *
     * @param int $mediaId
     * @return void
     */
    public function destroy($mediaId)
    {
        $this->client->request('DELETE', 'media/'.$mediaId, [
            'query' => ['force' => true]
        ]);
    }
}