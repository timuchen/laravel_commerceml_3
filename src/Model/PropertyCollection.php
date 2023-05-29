<?php

declare(strict_types=1);

namespace Timuchen\LaravelCommerceml3\Model;

use Timuchen\LaravelCommerceml3\ORM\Collection;

class PropertyCollection extends Collection
{

    public function getValue($propId, $valueId)
    {
        if (! is_null($prop = $this->get($propId))) {
            return $prop->getValue($valueId);
        }

        return null;
    }

    public function getName($propId)
    {
        if (! is_null($prop = $this->get($propId))) {
            return $prop->name;
        }

        return null;
    }
}
