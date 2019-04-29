<?php

namespace Extait\Articles\Block;

use Extait\Articles\Model\ArticleFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Sidebar
 * @package Extait\Articles\Block
 * @api
 */
class Sidebar extends Template
{
    /**
     * @var ArticleFactory
     */
    protected $articleFactory;

    /**
     * Sidebar constructor.
     * @param Context $context
     * @param ArticleFactory $articleFactory
     */
    public function __construct(
        Context $context,
        ArticleFactory $articleFactory
    ) {
        $this->articleFactory = $articleFactory;
        parent::__construct(
            $context
        );
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getArticles()
    {
        $article = $this->articleFactory->create();

        $collection = $article->getCollection();
        $orderedCollection = $collection->setOrder('published_at');

        return $orderedCollection->setPageSize(3);
    }

    /**
     * @param $id
     * @return string
     */
    public function getArticleUrl($id)
    {
        return $this->getUrl('extait_articles/index/article', ['id' => $id]);
    }
}
