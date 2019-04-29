<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;

class ProductLinkedRemove implements ObserverInterface
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

    public function execute(Observer $observer)
    {
        if (!empty($this->requestInterface->getParam('product')['current_product_id'])) {
            $productId = $this->requestInterface->getParam('product')['current_product_id'];

            $this->remove($productId);
        }
    }

    /**
     * @param $productId
     */
    protected function remove($productId)
    {
        $links = $this->linkFactory->create();

        $ids = $links->getLinkIdsByProductId($productId);

        $links->deleteBunch($ids);
    }
}
