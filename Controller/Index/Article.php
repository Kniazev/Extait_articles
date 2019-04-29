<?php

namespace Extait\Articles\Controller\Index;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Extait\Articles\Model\ArticleFactory;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;

/**
 * Class Article
 * @package Extait\Articles\Controller\Index
 */
class Article extends Action
{
    /**
     * @var \Extait\Articles\Model\ArticleFactory
     */
    protected $articleFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var RedirectFactory
     */
    protected $forward;

    /**
     * Article constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param ArticleFactory $articleFactory
     * @param ProductFactory $productFactory
     * @param LinkFactory $linkFactory
     * @param ForwardFactory $forwardFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ArticleFactory $articleFactory,
        ProductFactory $productFactory,
        LinkFactory $linkFactory,
        ForwardFactory $forwardFactory
    ) {
        $this->forward = $forwardFactory;
        $this->linkFactory = $linkFactory;
        $this->productFactory = $productFactory;
        $this->pageFactory = $pageFactory;
        $this->articleFactory = $articleFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $page_object = $this->pageFactory->create();

        $article = $this->initArticle();

        if (!empty($article)) {
            $products = $this->getRelatedProducts($article['id']);

            $page_object->getLayout()
                ->getBlock('extait_article')
                ->setData('article', $article)
                ->toHtml();
            $page_object
                ->getConfig()
                ->getTitle()
                ->set($article['title']);

            if (!empty($products)) {
                $page_object->getLayout()
                    ->getBlock('extait_article_related_products')
                    ->setData('products', $products)->toHtml();
            }
        } else {
            return $this->forward->create()->forward('noroute');
        }

        return $page_object;
    }

    /**
     * Get article.
     *
     * @return mixed
     */
    public function initArticle()
    {
        $articleId = (int)$this->getRequest()->getParam('id', false);

        $articleFactory = $this->articleFactory->create();
        $article = $articleFactory->load($articleId);

        return $article->getData();
    }

    /**
     * @param $articleId
     * @return mixed
     */
    public function getRelatedProducts($articleId)
    {
        if (!empty($articleId)) {
            $productsIds = $this->linkFactory->create()->getProductsIdsByArticle($articleId);

            if (!empty($productsIds)) {
                return $this->productFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('entity_id', ["in" => $productsIds])
                    ->addAttributeToSelect('*')
                    ->getItems();
            }
        }

        return null;
    }
}
