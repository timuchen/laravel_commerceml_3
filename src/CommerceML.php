<?php

namespace Timuchen\LaravelCommerceml3;

use Illuminate\Support\Facades\Storage;
use Timuchen\LaravelCommerceml3\Model\Category;
use Timuchen\LaravelCommerceml3\Model\CategoryCollection;
use Timuchen\LaravelCommerceml3\Model\Product;
use Timuchen\LaravelCommerceml3\Model\ProductCollection;
use Timuchen\LaravelCommerceml3\Model\PriceType;
use Timuchen\LaravelCommerceml3\Model\PriceTypeCollection;
use Timuchen\LaravelCommerceml3\Model\Property;
use Timuchen\LaravelCommerceml3\Model\PropertyCollection;
use Timuchen\LaravelCommerceml3\Model\Price;
use Timuchen\LaravelCommerceml3\Model\PriceCollection;
use Timuchen\LaravelCommerceml3\Model\Rest;
use Timuchen\LaravelCommerceml3\Model\Unit;
use Timuchen\LaravelCommerceml3\Model\UnitCollection;

class CommerceML {

    public $collections = [];

    public function __construct(){
        $this->collections = [
            'categories'     => new CategoryCollection(),
            'products'       => new ProductCollection(),
            'price_types'    => new PriceTypeCollection(),
            'offer_prices'   => new PriceCollection(),
            'properties_products'   => new PropertyCollection(),
            'properties_offers'     => new PropertyCollection(),
            'units'          => new UnitCollection(),
            'rests'          => new Rest(),
            'storages'       => new Storage(),
        ];
    }

    public function addXmls($fileName, $filePuth)
    {
        $fileType = stristr($fileName, "_", true);

        if ($fileType == "groups") {
            $fileXML = $this->loadXml($filePuth);
            $this->parseCategories($fileXML);
        }

        if ($fileType == "goods") {
            $fileXML = $this->loadXml($filePuth);
            $this->parseProducts($fileXML, false);
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
            $this->parseProperties($fileXML, false);
        }

        if ($fileType == "propertiesOffers") {
            $fileXML = $this->loadXml($filePuth);
            $this->parseProperties(false, $fileXML);
        }

        if ($fileType == "units") {
            $fileXML = $this->loadXml($filePuth);
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

            $this->getCollection('categories')->add($category);

            if ($xmlCategory->Группы) {
                $this->parseCategories($xmlCategory->Группы, $category);
            }
        }
    }

    public function parseProducts($goodsXml = false, $offersXml = false)
    {
        $buffer = [
            'product' => []
        ];

        if ($goodsXml) {
            if ($goodsXml->Каталог->Товары) {
                foreach ($goodsXml->Каталог->Товары->Товар as $product) {
                    $productId = (string)$product->Ид;
                    $buffer['product'][$productId]['import'] = $product;
                }
            }
        }

        if ($offersXml) {
            if ($offersXml->ПакетПредложений->Предложения) {
                foreach ($offersXml->ПакетПредложений->Предложения->Предложение as $offer) {
                    $offerId = (string)$offer->Ид;
                    $buffer['product'][$offerId]['offer'] = $offer;
                }
            }
        }

        foreach ($buffer['product'] as $item) {
            $import = isset($item['import']) ? $item['import'] : null;
            $offer  = isset($item['offer']) ? $item['offer'] : null;

            $product = new Product($import, $offer);
            $this->getCollection('products')->add($product);
        }
    }

    public function parsePriceTypes($priceListXml)
    {
        if ($priceListXml->Классификатор->ТипыЦен) {
            foreach ($priceListXml->Классификатор->ТипыЦен->ТипЦены as $xmlPriceType) {
                $priceType = new PriceType($xmlPriceType);
                $this->getCollection('price_types')->add($priceType);
            }
        }
    }

    public function parsePrices($pricesXml)
    {
            foreach ($pricesXml->ПакетПредложений->Предложения->Предложение as $xmlPrice){
                $price = new Price($xmlPrice);
                $this->getCollection('offer_prices')->add($price);
            }
    }

    public function parseUnits($unitsXml){

            foreach ($unitsXml->Классификатор->ЕдиницыИзмерения->ЕдиницаИзмерения as $xmlUnit){
                $unit = new Unit($xmlUnit);
                $this->getCollection('units')->add($unit);
            }

    }

    public function parseProperties($goodsXml = false, $offersXml = false)
    {
        if ($goodsXml) {
            foreach ($goodsXml->Классификатор->Свойства->Свойство as $xmlProperty) {
                $property = new Property($xmlProperty);
                $this->getCollection('properties_products')->add($property);
            }
        }

        if ($offersXml) {
            foreach ($offersXml->Классификатор->Свойства->Свойство as $xmlProperty) {
                $property = new Property($xmlProperty);
                $this->getCollection('properties_offers')->add($property);
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

