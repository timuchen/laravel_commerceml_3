<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Storage extends Model
{
    public $id;
    public string $name;
    public string $version;
    public string $delete;

    public function __construct($importXml = 0)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    public function loadImport($xml)
    {
        $this->id = (string) $xml->Ид;
        $this->name = (string) $xml->Наименование;
        $this->version = (string) $xml->НомерВерсии;
        $this->delete = (string) $xml->ПометкаУдаления;
    }
}
