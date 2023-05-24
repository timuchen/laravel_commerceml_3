<?php

namespace Timuchen\LaravelCommerceml3\Interfaces;


interface ExportOrdersSelf extends ExportSuccess
{
    /**
     * Формирование данных для 1С о заказа в интернет-магазине
     * @return mixed
     */
    public function getXML();
}
