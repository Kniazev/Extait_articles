<?php

namespace Extait\Articles\Model\ResourceModel\Article;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Link extends AbstractDb
{
    /**
     * Link constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table name and attributes table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link_article_products', 'id');
    }

    /**
     * @param $articleId
     * @param $productId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArticleLinkId($articleId, $productId)
    {
        $connection = $this->getConnection();

        $bind = [
            ':article_id' => (int)$articleId,
            ':product_id' => (int)$productId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['id']
        )->where(
            'article_id = :article_id'
        )->where(
            'product_id = :product_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLinkIdsByProductId($productId)
    {
        $connection = $this->getConnection();

        $bind = [
            ':product_id' => (int)$productId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['id']
        )->where(
            'product_id = :product_id'
        );

        return $connection->fetchAll($select, $bind);
    }

    /**
     * @param $articleId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLinkIdsByArticleId($articleId)
    {
        $connection = $this->getConnection();

        $bind = [
            ':article_id' => (int)$articleId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['id']
        )->where(
            'article_id = :article_id'
        );

        return $connection->fetchAll($select, $bind);
    }

    /**
     * @param $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteBunch($ids)
    {
        foreach ($ids as $id) {
            $connection = $this->getConnection();
            $connection->delete(
                $this->getMainTable(),
                [
                    'id = ?' => $id
                ]
            );
        }
    }

    /**
     * @param $id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [
                'id = ?' => $id
            ]
        );
    }

    /**
     * @param $productId
     * @param $linkedArticleId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveLinkedArticles($productId, $linkedArticleId)
    {
        $connection = $this->getConnection();

        $data = [
            'article_id' => $linkedArticleId,
            'product_id' => $productId
            ];

        $connection->insert(
            $this->getMainTable(),
            $data
        );
    }

    /**
     * @param $articleId
     * @param $linkedProductId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveLinkedProducts($articleId, $linkedProductId)
    {
        $connection = $this->getConnection();

        $data = [
            'article_id' => $articleId,
            'product_id' => $linkedProductId
        ];

        $connection->insert(
            $this->getMainTable(),
            $data
        );
    }

    /**
     * @param $articleId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsIdsByArticle($articleId)
    {
        $connection = $this->getConnection();

        $bind = [':article_id' => (int)$articleId];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id']
        )->where(
            'article_id = :article_id'
        );

        $childrenIds = [];
        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArticlesIdsByProduct($productId)
    {
        $articlesIds = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['article_id', 'product_id']
        )->where(
            'product_id IN(?)',
            $productId
        );

        $result = $connection->fetchAll($select);
        foreach ($result as $row) {
            $articlesIds[] = $row['product_id'];
        }

        return $articlesIds;
    }
}
