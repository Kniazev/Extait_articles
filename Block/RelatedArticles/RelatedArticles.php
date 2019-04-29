<?php

namespace Extait\Articles\Block\RelatedArticles;

use Extait\Articles\Model\ArticleFactory;
use Extait\Articles\Model\ArticleUrlRewriteGenerator;
use Extait\Articles\Model\ResourceModel\Article\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class RelatedArticles
 * @package Extait\Articles\Block\RelatedArticles
 * @api
 */
class RelatedArticles extends Template
{
    /**
     * @var ArticleFactory \
     */
    protected $collectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ArticleFactory
     */
    protected $articleFactory;

    /**
     * @var ArticleUrlRewriteGenerator
     */
    protected $rewriteGenerator;

    /**
     * RelatedArticles constructor.
     * @param Context $context
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param ArticleFactory $articleFactory
     * @param ArticleUrlRewriteGenerator $urlRewriteGenerator
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Registry $registry,
        ArticleFactory $articleFactory,
        ArticleUrlRewriteGenerator $urlRewriteGenerator,
        array $data = []
    ) {
        $this->rewriteGenerator = $urlRewriteGenerator;
        $this->articleFactory = $articleFactory;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * return related article
     * @return \Magento\Framework\DataObject[]
     */
    public function getRelatedArticles()
    {
        $productId = $this->getProductId();

        $collection = $this->collectionFactory
            ->create()
            ->getRelatedArticles($productId)
            ->getItems();

        $ids = [];

        foreach ($collection as $article) {
            $ids[] = $article['article_id'];
        }

        $linkedArticles = $this->articleFactory->create()
            ->getCollection()
            ->addFieldToFilter('id', ["in" => $ids])
            ->addFieldToFilter('published', ['eq' => true])
            ->getItems();

        return $linkedArticles;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        $product = $this->registry->registry('product');

        return $product->getId();
    }

    /**
     * @param $articleId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getArticleUrl($articleId)
    {
        return $this->rewriteGenerator->getRewriteUrl($articleId);
    }
}
