<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;

class ArticleLinkedRemove implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * ProductLinkedArticles constructor.
     * @param RequestInterface $requestInterface
     * @param LinkFactory $linkFactory
     */
    public function __construct(
        RequestInterface $requestInterface,
        LinkFactory $linkFactory
    ) {
        $this->requestInterface = $requestInterface;
        $this->linkFactory = $linkFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productId = $this->requestInterface->getParam('id');

        $this->remove($productId);
    }

    /**
     * Delete links by articleId
     * @param $articleId
     */
    protected function remove($articleId)
    {
        $links = $this->linkFactory->create();

        $ids = $links->getLinkIdsByArticleId($articleId);

        $links->deleteBunch($ids);
    }
}
