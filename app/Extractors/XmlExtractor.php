<?php

namespace App\Extractors;

use App\Machine;
use Corcel\Term;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;
use Taxonomy;

class XmlExtractor
{
    /**
     * Instance of the Xml service.
     *
     * @var \Sabre\Xml\Service
     */
    private $service;

    /**
     * XmlExtractor constructor.
     *
     * @param \Sabre\Xml\Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function process($xml)
    {
        $data = $this->parse($xml);

        $machine = $this->createMachine($data);

        $this->attachImages($machine, $data);

        dd($machine);
    }

    /**
     * @param $xml
     * @return array|object|string
     */
    private function parse($xml)
    {
        $service = new \Sabre\Xml\Service();
        $service->elementMap = [
            '{}voertuig' => function (Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader, '');
            }
        ];

        return $service->parse($xml);
    }

    private function createMachine(array $data)
    {
        $machine = new Machine([
            'post_title' => $data['merk'] . ' ' . $data['type'],
            'post_content' => $data['opmerkingen'],
            'post_type' => 'machine',
        ]);

        $machine->save();

        $term = Term::where('name', $data['merk'])->first();

        if (is_null($term)) {
            $term = new Term();
            $term->name = $data['merk'];
            $term->slug = str_slug($data['merk']);
            $term->save();
        }

        $taxonomy = Taxonomy::where(['taxonomy' => 'brand', 'description' => $data['merk']])->first();

        if (is_null($taxonomy)) {
            $taxonomy = new Taxonomy();
            $taxonomy->term_id = $term->term_id;
            $taxonomy->taxonomy = 'brand';
            $taxonomy->description = $data['merk'];
            $taxonomy->save();
        }

        $machine->taxonomies()->sync([$taxonomy->term_taxonomy_id]);

        $machine->meta->carrosserie = $data['carrosserie'];
        $machine->meta->brandstof = $data['brandstof'];
        $machine->meta->transmissie = $data['transmissie'];
        $machine->meta->btw_marge = $data['btw_marge'];
        $machine->meta->basiskleur = $data['basiskleur'];
        $machine->meta->bouwjaar = $data['bouwjaar'];

        $machine->save();

        return $machine;
    }

    private function attachImages($machine, $data)
    {
//        foreach ($data['afbeeldingen'] as $index => $image) {
//            foreach ($image['value'] as $fields) {
//                if ($fields['name'] == '{}url') {
//                    $key = 'images'.$index;
//                    $machine->meta->{$key} = $fields['value'];
//                }
//            }
//        }

        $images = array_map(function ($image) {
            foreach ($image['value'] as $fields) {
                if ($fields['name'] == '{}url') {
                    return $fields['value'];
                }
            }
            return null;
        }, $data['afbeeldingen']);

        $machine->meta->images = serialize($images);


        $machine->save();
    }
}