<?php

namespace App\Handlers;

use App\Repositories\BrandRepository;
use Illuminate\Http\Request;
use App\Repositories\PostRepository;
use App\Repositories\MediaRepository;

class PostRequest
{
    /**
     * Instance of the post repository.
     *
     * @var \App\Repositories\PostRepository
     */
    private $postRepo;

    /**
     * Instance of the media repository.
     *
     * @var \App\Repositories\MediaRepository
     */
    private $mediaRepo;

    /**
     * Instance of the brand repository.
     *
     * @var \App\Repositories\BrandRepository
     */
    private $brandRepo;

    /**
     * PostRequest constructor.
     *
     * @param \App\Repositories\PostRepository  $postRepo
     * @param \App\Repositories\MediaRepository $mediaRepo
     * @param \App\Repositories\BrandRepository $brandRepo
     */
    public function __construct(PostRepository $postRepo, MediaRepository $mediaRepo, BrandRepository $brandRepo)
    {
        $this->postRepo = $postRepo;
        $this->mediaRepo = $mediaRepo;
        $this->brandRepo = $brandRepo;
    }

    public function handle($request)
    {
        switch ($request->actie) {
            case 'add': return $this->createNew($request);
            case 'change': return $this->updatePost($request);
            case 'delete': return $this->deletePost($request);
            default: throw new \InvalidArgumentException('The action can only be "add", "change" or "delete".');
        }
    }

    private function generateImages($request, $post)
    {
        $images = collect(explode(',', $request->afbeeldingen));

        return $images->filter(function ($image) {
            return ! empty($image);
        })->map(function ($image) use ($post) {
            $media = $this->mediaRepo->createMedia($image);

            $this->mediaRepo->attachToPost($media->id, $post->id);

            return $media;
        });
    }

    private function setBrand($post, $request)
    {
        $brand = $this->brandRepo->getByName($request->merk);

        if (! $brand) {
            $brand = $this->brandRepo->createBrand($request->merk);
        }

        $this->postRepo->attachToBrand($post->id, $brand->id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \stdClass
     */
    private function createNew(Request $request)
    {
        $post = $this->postRepo->createPost([
            'title'        => $request->merk . ' ' . $request->type,
            'slug'         => $request->voertuignr_hexon,
            'content'      => $request->opmerkingen,
            'status'       => 'publish',
            'body'         => $request->carrosserie,
            'fuel'         => $request->brandstof,
            'transmission' => $request->transmissie,
            'vat'          => $request->btw_marge,
            'basecolor'    => $request->basiskleur,
            'buildyear'    => $request->bouwjaar,
            'price'        => $request->prijstype,
            'condition'    => $request->staat_algemeen,
        ]);

        $images = $this->generateImages($request, $post);

        if (!$images->isEmpty()) {
            $this->postRepo->setFeaturedImage($post->id, $images->first()->id);
        }

        $this->setBrand($post, $request);

        return $post;
    }

    private function updatePost($request)
    {
        $post = $this->postRepo->findBySlug($request->voertuignr_hexon);

        if ($post) {
            $this->postRepo->update($post->id, [
                'title'        => $request->merk . ' ' . $request->type,
                'content'      => $request->opmerkingen,
                'status'       => 'publish',
                'body'         => $request->carrosserie,
                'fuel'         => $request->brandstof,
                'transmission' => $request->transmissie,
                'vat'          => $request->btw_marge,
                'basecolor'    => $request->basiskleur,
                'buildyear'    => $request->bouwjaar,
                'price'        => $request->prijstype,
                'condition'    => $request->staat_algemeen,
            ]);
        }
    }

    private function deletePost($request)
    {
        $post = $this->postRepo->findBySlug($request->voertuignr_hexon);

        if ($post) {
            if ($post->_links) {
                if ($post->_links->{'wp:attachment'}) {
                    if ($post->_links->{'wp:attachment'}[0]) {
                        $medias = $this->mediaRepo->getByUrl($post->_links->{'wp:attachment'}[0]->href);

                        foreach ($medias as $media) {
                            $this->mediaRepo->destroy($media->id);
                        }
                    }
                }
            }

            $this->postRepo->destroy($post->id);
        }
    }
}