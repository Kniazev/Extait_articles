<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\App\RequestInterface;
use Extait\Articles\Model\ArticleUrlRewriteGenerator;
use Extait\Articles\Model\ResourceModel\Article\LinkFactory;

/**
 * Class TestObserver
 */
class ArticleUrlRewrite implements ObserverInterface
{
    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var ArticleUrlRewriteGenerator
     */
    protected $urlGenerator;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * ArticleUrlRewrite constructor.
     * @param UrlPersistInterface $urlPersist
     * @param ArticleUrlRewriteGenerator $urlGenerator
     * @param LinkFactory $linkFactory
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        ArticleUrlRewriteGenerator $urlGenerator,
        LinkFactory $linkFactory,
        RequestInterface $requestInterface
    ) {
        $this->requestInterface = $requestInterface;
        $this->linkFactory = $linkFactory;
        $this->urlPersist = $urlPersist;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $article = $observer->getEvent()->getObject();

        $urls = [];

        if ($article->dataHasChangedFor('title')) {
            $urls[] = $this->urlGenerator->createUrlRewrite($article);

            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $article->getId(),
                UrlRewrite::ENTITY_TYPE => ArticleUrlRewriteGenerator::ENTITY_TYPE,
            ]);
            $this->urlPersist->replace($urls);
        }

        $linkedProducts = $this->requestInterface->getParam('related_product_listing')['related'];

        if ($linkedProducts != null) {
            $this->save($article->getId(), $linkedProducts);
        }
    }

    /**
     * @param $articleId
     * @param $linkedProducts
     */
    protected function save($articleId, $linkedProducts)
    {
        $links = $this->linkFactory->create();

        foreach ($linkedProducts as $linkedProduct) {
            $links->saveLinkedProducts($articleId, $linkedProduct['id']);
        }
    }
}
