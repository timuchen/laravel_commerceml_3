<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Price extends Model {

    /*
     * Product ID
     */
    public $id;
    public $OfferId;
    public $type;
    public $currency;
    public $value;
    public $view;
    public $tax =[];

    public function __construct($importXml = null)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    private function loadImport($xml)
    {
        $universalID = (string) $xml->Ид;
        $universalID = explode("#", $universalID);
        $this->id = $universalID[1];
        $this->OfferId = $universalID[0];

        foreach ($xml->Цены->Цена as $price) {

            $this->type = (string)$price->ИдТипаЦены;
            $this->currency = (string)$price->Валюта;
            $this->value = (float)$price->ЦенаЗаЕдиницу;
            $this->view = (string)$price->Представление;
            $this->tax = [
                  'tax_name' => (string) $price->Налог->Наименование,
                  'in_amount' => (string) $price->Налог->УчтеноВСумме,
            ];
        }
    }

}
