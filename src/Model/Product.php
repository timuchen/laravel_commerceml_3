<?php

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Product extends Model
{
    public $id;
    public $productOfferId;
    public $name;
    public $sku;
    public $quantity;
    public $unit;
    public $description;
    public $delete;
    public $barcode;

    public $country;
    public $width;
    public $length;
    public $height;
    public $version;
    public $manufacturer = [];

    public $price = [];
    public $categories = [];
    public $requisites = [];
    public $properties = [];
    public $propertyComplex = [];
    public $characteristics = [];
    public $imageURL =[];

    public function __construct($importXml = null, $offersXml = null)
    {
        $this->name        = '';
        $this->quantity    = 0;
        $this->description = '';

        if (!is_null($importXml)) {
            $this->loadImport($importXml);
        }

        if (!is_null($offersXml)) {
            $this->loadOffers($offersXml);
        }
    }

    public function loadImport($xml)
    {

        $this->id          = trim($xml->Ид);
        $this->name        = trim($xml->Наименование);
        $this->description = trim($xml->Описание);
        $this->delete      = trim($xml->ПометкаУдаления);
        $this->sku         = trim($xml->Артикул);
        $this->unit        = trim($xml->БазоваяЕдиница);
        $this->barcode     = trim($xml->Штрихкод);
        $this->country     = trim($xml->Страна);
        $this->width       = trim($xml->Ширина);
        $this->length      = trim($xml->Длина);
        $this->height      = trim($xml->Высота);
        $this->version     = trim($xml->НомерВерсии);



        if ($xml->Группы) {
            foreach ($xml->Группы->Ид as $categoryId) {
                $this->categories[] = (string)$categoryId;
            }
        }

        if($xml->Изготовитель){
                $this->manufacturer = [
                    'id' => (string)$xml->Изготовитель->Ид,
                    'name' => (string)$xml->Изготовитель->Наименование,
                    'oficial_name' => (string)$xml->Изготовитель->ОфициальноеНаименование,
                    ];
        }

        if ($xml->ЗначенияРеквизитов) {
            foreach ($xml->ЗначенияРеквизитов->ЗначениеРеквизита as $value) {
                $name = (string)$value->Наименование;
                $this->requisites[$name] = (string)$value->Значение;
            }
        }

        if ($xml->ЗначенияСвойств) {
            foreach ($xml->ЗначенияСвойств->ЗначенияСвойства as $prop) {

                $id    = (string)$prop->Ид;
                $value = (string)$prop->Значение;

                if ($value) {
                    $pattern = '/[a-z0-9]{8}+-[a-z0-9]{4}+-[a-z0-9]{4}+-[a-z0-9]{4}+-[a-z0-9]{12}/';
                    if (preg_match($pattern, $value)){
                        $this->propertyComplex[$id] = $value;
                    }else {
                        $this->properties[$id] = $value;
                    }
                }
            }
        }
    }

    public function loadOffers($xml)
    {
        $universalID = trim($xml->Ид);
        $universalID = explode("#", $universalID);
        $this->id = $universalID[1];
        $this->productOfferId = $universalID[0];

        $this->name        = trim($xml->Наименование);
        $this->delete = trim($xml->ПометкаУдаления);

        $this->sku  = trim($xml->Артикул);

        if ($xml->ХарактеристикиТовара) {
            foreach ($xml->ХарактеристикиТовара->ХарактеристикаТовара as $value) {
                $name = (string)$value->Наименование;
                $this->characteristics[$name] = (string)$value->Значение;
            }
        }

        if ($xml->ЗначенияРеквизитов) {
            foreach ($xml->ЗначенияРеквизитов->ЗначениеРеквизита as $value) {
                $name = (string)$value->Наименование;
                $this->requisites[$name] = (string)$value->Значение;
            }
        }

        if ($xml->ЗначенияСвойств) {
            foreach ($xml->ЗначенияСвойств->ЗначенияСвойства as $prop) {

                $id    = (string)$prop->Ид;
                $value = (string)$prop->Значение;

                if ($value) {
                    $pattern = '/[a-z0-9]{8}+-[a-z0-9]{4}+-[a-z0-9]{4}+-[a-z0-9]{4}+-[a-z0-9]{12}/';
                    if (preg_match($pattern, $value)){
                        $this->propertyComplex[$id] = $value;
                    }else {
                        $this->properties[$id] = $value;
                    }
                }
            }
        }

        if ($xml->Картинка) {
            foreach ($xml->Картинка as $image){
                $this->imageURL[] = $image;
            }
        }
    }

    // TODO: переписать для цен
    public function loadOffersPrice($xml)
    {
        if ($xml->Количество) {
            $this->quantity = (int)$xml->Количество;
        }

        if ($xml->Цены) {
            foreach ($xml->Цены->Цена as $price) {
                $id = (string)$price->ИдТипаЦены;

                $this->price[$id] = [
                    'type'     => $id,
                    'currency' => (string)$price->Валюта,
                    'value'    => (float)$price->ЦенаЗаЕдиницу
                ];
            }
        }
    }

    public function getPrice($type)
    {
        foreach ($this->price as $price) {
            if ($price['type'] == $type) {
                return $price['value'];
            }
        }

        return 0;
    }
}
