<?php

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Category extends Model
{

    public $id;
    public $name;
    public $parent;

    public function __construct($importXml = null)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    public function loadImport($xml)
    {
        $this->id = (string) $xml->Ид;

        $this->name = (string) $xml->Наименование;
    }


    public function addChild($category)
    {
        $category->parent = $this->id;
    }


    public function attachProducts($products)
    {
        $this->products = array();
        foreach ($products->fetch() as $product) {
            if (array_key_exists($this->id, $product->categories)) {
                $this->products[$product->id] = $product;
            }
        }
    }
}
