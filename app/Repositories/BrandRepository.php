<?php

namespace App\Repositories;

class BrandRepository extends Repository
{
    /**
     * Get a brand by its name.
     *
     * @param string $name
     * @return \stdClass|bool
     */
    public function getByName($name)
    {
        $response = $this->client->request('GET', 'brand', [
            'query' => [
                'per_page' => 100
            ]
        ]);

        $brands = $this->decodeResponse($response);

        $filtered = array_filter($brands, function ($brand) use ($name) {
            return $brand->slug == str_slug($name);
        });

        if (count($filtered)) return head($filtered);

        return false;
    }

    /**
     * Create a new brand.
     *
     * @param $name
     * @return \stdClass
     */
    public function createBrand($name)
    {
        $response = $this->client->request('POST', 'brand', [
            'form_params' => [
                'name' => $name,
                'slug' => str_slug($name),
            ]
        ]);

        return $this->decodeResponse($response);
    }
}