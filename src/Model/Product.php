<?php

declare(strict_types=1);

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
        $this->id          = (string) $xml->Ид;
        $this->name        = (string) $xml->Наименование;
        $this->description = (string) $xml->Описание;
        $this->delete      = (string) $xml->ПометкаУдаления;
        $this->sku         = (string) $xml->Артикул;
        $this->unit        = (string) $xml->БазоваяЕдиница;
        $this->barcode     = (string) $xml->Штрихкод;
        $this->country     = (string) $xml->Страна;
        $this->width       = (string) $xml->Ширина;
        $this->length      = (string) $xml->Длина;
        $this->height      = (string) $xml->Высота;
        $this->version     = (string) $xml->НомерВерсии;

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

}
