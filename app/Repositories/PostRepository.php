<?php

namespace App\Repositories;

class PostRepository extends Repository
{
    /**
     * Create a new post.
     *
     * @param array $attributes
     * @return \stdClass
     */
    public function createPost(array $attributes)
    {
        $response = $this->client->request('POST', 'machine', [
            'form_params' => $attributes
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Set the featured image of the post.
     *
     * @param int $postId
     * @param int $mediaId
     * @return void
     */
    public function setFeaturedImage($postId, $mediaId)
    {
        $this->client->request('POST', 'machine/'.$postId, [
            'form_params' => [ 'featured_media' => $mediaId]
        ]);
    }

    /**
     * Attach the post to the given brand.
     *
     * @param int $postId
     * @param int $brandId
     * @return void
     */
    public function attachToBrand($postId, $brandId)
    {
        $this->client->request('POST', 'machine/'.$postId, [
            'headers' => ['Authorization' => 'Basic Um9iOkZvcm11bGUx'],
            'form_params' => [
                'brand' => [$brandId]
            ]
        ]);
    }

    public function findBySlug($slug)
    {
        $response = $this->client->request('GET', 'machine', [
            'query' => ['slug' => $slug]
        ]);

        $post = $this->decodeResponse($response);

        if (count($post)) return $post[0];

        return false;
    }

    public function update($postId, array $attributes)
    {
        $response = $this->client->request('POST', 'machine/'.$postId, [
            'form_params' => $attributes
        ]);

        return $this->decodeResponse($response);
    }

    public function destroy($postId)
    {
        $this->client->request('DELETE', 'machine/'.$postId, [
            'query' => ['force' => true]
        ]);
    }
}