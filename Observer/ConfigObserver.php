<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Extait\Articles\Model\ArticleUrlRewriteGenerator;
use Extait\Articles\Model\ArticleFactory;

class ConfigObserver implements ObserverInterface
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ArticleFactory
     */
    protected $articleFactory;

    /**
     * @var ArticleUrlRewriteGenerator
     */
    protected $urlRewriteGenerator;

    /**
     * ConfigObserver constructor.
     * @param UrlPersistInterface $urlPersist
     * @param RequestInterface $request
     * @param ArticleFactory $articleFactory
     * @param ArticleUrlRewriteGenerator $urlRewriteGenerator
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        RequestInterface $request,
        ArticleFactory $articleFactory,
        ArticleUrlRewriteGenerator $urlRewriteGenerator
    ) {
        $this->articleFactory = $articleFactory;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $collection = $this->articleFactory->create()
            ->getCollection();

        foreach ($collection->getItems() as $item) {
            $urls = [];

            $urls[] = $this->urlRewriteGenerator->createUrlRewrite($item);

            $this->urlPersist->replace($urls);
        }
    }
}
