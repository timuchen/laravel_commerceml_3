<?php

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Price extends Model {

    public $id;
    public $name;
    public $values = array();

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
        $valueType = (string) $xml->ТипЗначений;

        if ($valueType == 'Справочник' && $xml->ВариантыЗначений) {
            foreach ($xml->ВариантыЗначений->Справочник as $value) {
                $id = (string) $value->ИдЗначения;
                $this->values[$id] = (string) $value->Значение;
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
