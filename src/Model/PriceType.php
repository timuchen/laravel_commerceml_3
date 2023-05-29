<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class PriceType extends Model
{
    public $id;
    public $type;
    public $currency;
    public $version;
    public $delete;
    public $tax = [];

    public function __construct($xmlPriceType = null)
    {
        if (! is_null($xmlPriceType)) {
            $this->loadImport($xmlPriceType);
        }
    }

    private function loadImport($xmlPriceType)
    {
        $this->id = (string) $xmlPriceType->Ид;
        $this->type = (string) $xmlPriceType->Наименование;
        $this->currency = (string) $xmlPriceType->Валюта;
        $this->version = (string) $xmlPriceType->НомерВерсии;
        $this->delete = (string) $xmlPriceType->ПометкаУдаления;
        $this->tax = [
            'tax_name' => (string) $xmlPriceType->Налог->Наименование[0],
            'in_amount' => (string) $xmlPriceType->Налог->УчтеноВСумме[0],
            'excise' => (string) $xmlPriceType->Налог->Акциз[0],
        ];
    }
}
