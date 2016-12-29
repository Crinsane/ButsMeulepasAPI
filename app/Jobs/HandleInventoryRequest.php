<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Repositories\PostRepository;
use App\Repositories\BrandRepository;
use App\Repositories\MediaRepository;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleInventoryRequest implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
     * The request data.
     *
     * @var array
     */
    private $request;

    /**
     * PostRequest constructor.
     *
     * @param array $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\PostRepository  $postRepo
     * @param \App\Repositories\MediaRepository $mediaRepo
     * @param \App\Repositories\BrandRepository $brandRepo
     * @return void
     */
    public function handle(PostRepository $postRepo, MediaRepository $mediaRepo, BrandRepository $brandRepo)
    {
        $this->postRepo = $postRepo;
        $this->mediaRepo = $mediaRepo;
        $this->brandRepo = $brandRepo;

        switch ($this->request['actie']) {
            case 'add': return $this->createNew();
            case 'change': return $this->updatePost();
            case 'delete': return $this->deletePost();
            default: throw new \InvalidArgumentException('The action can only be "add", "change" or "delete".');
        }
    }

    private function generateImages($post)
    {
        $images = collect(explode(',', $this->request['afbeeldingen']));

        return $images->filter(function ($image) {
            return ! empty($image);
        })->map(function ($image) use ($post) {
            $media = $this->mediaRepo->createMedia($image);

            $this->mediaRepo->attachToPost($media->id, $post->id);

            return $media;
        });
    }

    private function setBrand($post)
    {
        $brand = $this->brandRepo->getByName($this->request['merk']);

        if (! $brand) {
            $brand = $this->brandRepo->createBrand($this->request['merk']);
        }

        $this->postRepo->attachToBrand($post->id, $brand->id);
    }

    private function createNew()
    {
        $post = $this->postRepo->createPost([
            'title'        => array_get($this->request, 'merk') . ' ' . array_get($this->request, 'type'),
            'slug'         => array_get($this->request, 'voertuignr_hexon'),
            'content'      => array_get($this->request, 'opmerkingen'),
            'status'       => 'publish',
            'body'         => array_get($this->request, 'carrosserie'),
            'fuel'         => array_get($this->request, 'brandstof'),
            'transmission' => array_get($this->request, 'transmissie'),
            'vat'          => array_get($this->request, 'btw_marge'),
            'basecolor'    => array_get($this->request, 'basiskleur'),
            'buildyear'    => array_get($this->request, 'bouwjaar'),
            'price'        => array_get($this->request, 'prijstype'),
            'condition'    => array_get($this->request, 'staat_algemeen'),
        ]);

        $images = $this->generateImages($post);

        if (!$images->isEmpty()) {
            $this->postRepo->setFeaturedImage($post->id, $images->first()->id);
        }

        try {
            $this->setBrand($post);
        } catch (\Exception $e) {
            Log::error('Error while setting the brand to '.$this->request['merk'].' . '.$e->getMessage());
        }

        return $post;
    }

    private function updatePost()
    {
        $post = $this->postRepo->findBySlug($this->request['voertuignr_hexon']);

        if ($post) {
            $this->postRepo->update($post->id, [
                'title'        => array_get($this->request, 'merk') . ' ' . array_get($this->request, 'type'),
                'content'      => array_get($this->request, 'opmerkingen'),
                'status'       => 'publish',
                'body'         => array_get($this->request, 'carrosserie'),
                'fuel'         => array_get($this->request, 'brandstof'),
                'transmission' => array_get($this->request, 'transmissie'),
                'vat'          => array_get($this->request, 'btw_marge'),
                'basecolor'    => array_get($this->request, 'basiskleur'),
                'buildyear'    => array_get($this->request, 'bouwjaar'),
                'price'        => array_get($this->request, 'prijstype'),
                'condition'    => array_get($this->request, 'staat_algemeen'),
            ]);
        }
    }

    private function deletePost()
    {
        $post = $this->postRepo->findBySlug($this->request['voertuignr_hexon']);

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
