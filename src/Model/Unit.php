<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Model;

class Unit extends Model {
    public $id;
    public $version;
    public $delete;
    public $shortName;
    public $code;
    public $name;
    public $internationalReduction;

    public function __construct($xmlUnit = null)
    {
        if (! is_null($xmlUnit)) {
            $this->loadImport($xmlUnit);
        }
    }

    private function loadImport($xmlUnit)
    {
        $this->id = (string) $xmlUnit->Ид;
        $this->version = (string) $xmlUnit->НомерВерсии;
        $this->delete = (string) $xmlUnit->ПометкаУдаления;
        $this->shortName = (string) $xmlUnit->НаименованиеКраткое;
        $this->code = (string) $xmlUnit->Код;
        $this->name = (string) $xmlUnit->НаименованиеПолное;
        $this->internationalReduction =(string) $xmlUnit->МеждународноеСокращение;
    }

}
