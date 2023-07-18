<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Storage extends Model
{
    public $id;
    public string $name;
    public string $version;
    public bool $delete;

    public function __construct($importXml = 0)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    public function loadImport($xml)
    {
        $this->id = $xml->Ид;
        $this->name = (string) $xml->Наименование;
        $this->version = (string) $xml->НомерВерсии;
        $this->delete = (bool) $xml->ПометкаУдаления;
    }
}
