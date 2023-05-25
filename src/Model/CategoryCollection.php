<?php

    namespace Timuchen\LaravelCommerceml3\Model;

    use Timuchen\LaravelCommerceml3\ORM\Collection;

    class CategoryCollection extends Collection
    {

        public function attachProductCollection($productCollection)
        {
            foreach ($this->fetch() as $category) {
                $category->attachProducts($productCollection);
            }
        }
    }
