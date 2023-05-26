<?php

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Collection;

class PriceTypeCollection extends Collection
{
    public function getType($type)
    {
        return $this->get($type)->type;
    }
}
