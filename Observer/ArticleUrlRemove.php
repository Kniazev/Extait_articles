<?php

namespace Extait\Articles\Observer;

use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ArticleUrlRemove implements ObserverInterface
{
    const ENTITY_TYPE = 'extait_article';

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        UrlPersistInterface $urlPersist
    ) {
        $this->urlPersist = $urlPersist;
    }

    /**
     * Remove product urls from storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $article = $observer->getEvent()->getObject();

        if ($article->getId()) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $article->getId(),
                    UrlRewrite::ENTITY_TYPE => self::ENTITY_TYPE,
                ]
            );
        }
    }
}
