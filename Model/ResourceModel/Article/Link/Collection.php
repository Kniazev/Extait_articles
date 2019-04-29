<?php

namespace Extait\Articles\Model\ResourceModel\Article\Link;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Extait\Articles\Model\Article\Link as ModelLink;
use Extait\Articles\Model\ResourceModel\Article\Link;

class Collection extends AbstractCollection
{
    /**
     * Product object
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    protected $article;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelLink::class,
            Link::class
        );
    }
}
