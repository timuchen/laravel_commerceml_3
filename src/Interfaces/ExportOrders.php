<?php

namespace Timuchen\LaravelCommerceml3\Interfaces;

use CommerceML\Implementation\CommercialInformation as CommercialBase;

interface ExportOrders extends ExportSuccess
{
    /**
     * @return \CommerceML\Implementation\CommercialInformation Данные о
     * заказах, соответствувющие тегу "Коммерческая информация"
     */
    public function exportAllOrders(): CommercialBase;
}
