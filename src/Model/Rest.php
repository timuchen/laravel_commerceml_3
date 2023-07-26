<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Rest extends Model
{
    public $id;
    public $stores = array();

    public function __construct($importXml = 0)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    public function loadImport($xml)
    {
        $this->id = (string) $xml->Ид;
        $storesXml = $xml->Остатки;

        if ($storesXml->Остаток->Склад){
            foreach ($storesXml->Остаток as $store) {
                $id = (string) $store->Склад->Ид;
                $this->stores[$id] = (string) $store->Склад->Количество;
            }
        }
    }
}
