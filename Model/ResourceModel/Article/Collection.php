<?php

namespace Extait\Articles\Model\ResourceModel\Article;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'extait_articles_collection';
    protected $_eventObject = 'articles_collection';

    protected function _construct()
    {
        $this->_init(\Extait\Articles\Model\Article::class, \Extait\Articles\Model\ResourceModel\Article::class);
    }

    /**
     * @param $productId
     * @return $this
     */
    public function getRelatedArticles($productId)
    {
        $this->getSelect()->join(
            ['related' => $this->getTable('link_article_products')],
            'main_table.id = related.article_id',
            '*'
        )->where(
            'related.product_id = ?',
            $productId
        );

        return $this;
    }
}
