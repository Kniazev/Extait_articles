<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;

class ProductLinkedArticles implements ObserverInterface
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
     * @var ModelFactory
     */
    protected $modelFactory;

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

    public function execute(Observer $observer)
    {
        if (isset($this->requestInterface->getParam('links')['article'])) {
            $productId = $this->requestInterface->getParam('product')['current_product_id'];

            $linkedArticles = $this->requestInterface->getParam('links')['article'];

            $this->save($productId, $linkedArticles);
        }
    }

    /**
     * @param $productId
     * @param $linkedArticles
     */
    protected function save($productId, $linkedArticles)
    {
        $links = $this->linkFactory->create();

        foreach ($linkedArticles as $linkedArticle) {
            $links->saveLinkedArticles($productId, $linkedArticle['id']);
        }
    }
}
