<?php

namespace Extait\Articles\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Extait\Articles\Helper\EmailHelper;
use Extait\Articles\Model\ResourceModel\Article\CollectionFactory;
use Extait\Articles\Model\ArticleUrlRewriteGenerator;

/**
 * Class Email
 * @package Extait\Articles\Observer
 */
class Email implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var CollectionFactory
     */
    protected $articleCollection;

    /**
     * @var ArticleUrlRewriteGenerator
     */
    protected $articleUrlRewriteGenerator;

    /**
     * Email constructor.
     * @param EmailHelper $emailHelper
     * @param ArticleUrlRewriteGenerator $articleUrlRewriteGenerator
     * @param CollectionFactory $articleCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EmailHelper $emailHelper,
        ArticleUrlRewriteGenerator $articleUrlRewriteGenerator,
        CollectionFactory $articleCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->articleCollection = $articleCollection;
        $this->articleUrlRewriteGenerator = $articleUrlRewriteGenerator;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $articles = [];

        $data = $observer->getEvent()->getOrder();
        $customerEmail = $data->getCustomerEmail();
        $productId = $data->getData()['items'][0]->getData()['product_id'];
        $productName = $data->getData()['items'][0]->getData()['name'];

        $articles['productName'] = $productName;
        $articles['list'] = $this->getArticlesList($productId);

        if (isset($articles['list'])) {
            $this->sendEmail($articles, $customerEmail);
        }
    }

    /**
     * @param $emailTemplateVariables
     * @param $receiverInfo
     */
    protected function sendEmail($emailTemplateVariables, $receiverInfo)
    {
        $this->emailHelper->sendEmail($emailTemplateVariables, $receiverInfo);
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getArticlesList($productId)
    {
        $list = [];

        foreach ($this->getArticles($productId) as $key => $linkedArticle) {
            $id = $linkedArticle->getArticleId();
            $url = $this->articleUrlRewriteGenerator->getRewriteUrl($id);
            $list[$key]['url'] = $url;
            $list[$key]['title'] = $linkedArticle->getTitle();
        }

        return $list;
    }

    /**
     * @param $productId
     * @return mixed
     */
    protected function getArticles($productId)
    {
        return $this->articleCollection
            ->create()
            ->getRelatedArticles($productId)
            ->getItems();
    }
}
