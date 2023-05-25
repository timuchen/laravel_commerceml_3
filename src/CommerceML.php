<?php

namespace Timuchen\LaravelCommerceml3;

use Timuchen\LaravelCommerceml3\ORM\Collection;
use Timuchen\LaravelCommerceml3\Model\Category;
use Timuchen\LaravelCommerceml3\Model\CategoryCollection;

class CommerceML {

    protected $collections = [];

    public function __construct(){
        $this->collections = [
            'category'      => new CategoryCollection(),
            'product',
            'priceType',
            'property'
        ];
    }

    public function addXmls($fileName, $filePuth)
    {
        $fileType = stristr($fileName, "_", true);

        if ($fileType == "goods") {
            $fileXML = $this->loadXml($filePuth);
            $this->parseProducts($fileXML, false);
        }

        if ($fileType == "groups") {
            $fileXML = $this->loadXml($filePuth);
            return $this->parseCategories($fileXML);
        }

        if ($fileType == "offers") {
            $fileXML = $this->loadXml($filePuth);
            $this->parseProducts(false, $fileXML);
        }

        if ($fileType == "priceLists") {
            $fileXML = $this->loadXml($filePuth);
            $this->parsePriceTypes($fileXML);
        }

        if ($fileType == "prices") {
            $fileXML = $this->loadXml($filePuth);
            $this->parsePrices($fileXML);
        }

        if ($fileType == "propertiesGoods") {
            $fileXML = $this->loadXml($filePuth);
            $this->parsePropertiesGoods($fileXML);
        }

        if ($fileType == "propertiesOffers") {
            $fileXML = $this->loadXml($filePuth);
            $this->parsePropertiesOffers($fileXML);
        }

        if ($fileType == "units") {
            $fileXML = $this->loadXml($fileName);
            $this->parseUnits($fileXML);
        }

    }


    public function parseCategories($groupsXml, $parent = null)
    {
        $xmlCategories = ($groupsXml->Классификатор->Группы)
            ? $groupsXml->Классификатор->Группы
            : $xmlCategories = $groupsXml;

        foreach ($xmlCategories->Группа as $xmlCategory) {

            $category = new Category($xmlCategory);

            if (!is_null($parent)) {
                $parent->addChild($category);
            }

            $this->getCollection("category")->add($category);

            if ($xmlCategory->Группы) {
                $this->parseCategories($xmlCategory->Группы, $category);
            }
        }
    }

    public function parseProducts($goodsXml = false, $offersXml = false)
    {
        $buffer = [
            'products' => []
        ];

        if ($goodsXml) {
            if ($goodsXml->Каталог->Товары) {
                foreach ($goodsXml->Каталог->Товары->Товар as $product) {
                    $productId = (string)$product->Ид;
                    $buffer['products'][$productId]['import'] = $product;
                }
            }
        }

        if ($offersXml) {
            if ($offersXml->ПакетПредложений->Предложения) {
                foreach ($offersXml->ПакетПредложений->Предложения->Предложение as $offer) {
                    $productId                               = (string)$offer->Ид;
                    $buffer['products'][$productId]['offer'] = $offer;
                }
            }
        }

        foreach ($buffer['products'] as $item) {
            $import = isset($item['import']) ? $item['import'] : null;
            $offer  = isset($item['offer']) ? $item['offer'] : null;

            $product = new Product($import, $offer);
            $this->getCollection('product')->add($product);
        }
    }

    public function parsePriceTypes($offersXml)
    {
        if ($offersXml->ПакетПредложений->ТипыЦен) {
            foreach ($offersXml->ПакетПредложений->ТипыЦен->ТипЦены as $xmlPriceType) {
                $priceType = new PriceType($xmlPriceType);
                $this->getCollection('priceType')->add($priceType);
            }
        }
    }

    public function parseProperties($importXml)
    {
        if ($importXml->Классификатор->Свойства) {
            foreach ($importXml->Классификатор->Свойства->Свойство as $xmlProperty) {
                $property = new Property($xmlProperty);
                $this->getCollection('property')->add($property);
            }

        }
    }

    public function getCollection($name)
    {
        return $this->collections[$name];
    }


    private function loadXml($xml)
    {
        return is_file($xml)
            ? simplexml_load_file($xml)
            : simplexml_load_string($xml);
    }

}

