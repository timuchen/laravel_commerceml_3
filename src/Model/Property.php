<?php

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Property extends Model{

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

    public function getValue($valueId)
    {
        if (isset($this->values[$valueId])) {
            return $this->values[$valueId];
        }

        return null;
    }
}
