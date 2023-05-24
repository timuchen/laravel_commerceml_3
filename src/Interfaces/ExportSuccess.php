<?php


namespace Timuchen\LaravelCommerceml3\Interfaces;

interface ExportSuccess
{
    /**
     * Метод, вызываемый контроллером после того, как данные были выгружены на
     * сервер
     * @return string|null
     */
    public function stepSuccess(): ?string;
}
