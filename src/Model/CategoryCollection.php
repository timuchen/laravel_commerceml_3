<?php
    declare(strict_types=1);
    namespace Timuchen\LaravelCommerceml3\Model;

    use Timuchen\LaravelCommerceml3\ORM\Collection;

    class CategoryCollection extends Collection
    {

        public function getCategoryes(){

        }
        public function attachProductCollection($productCollection)
        {
            foreach ($this->fetch() as $category) {
                $category->attachProducts($productCollection);
            }
        }
    }
