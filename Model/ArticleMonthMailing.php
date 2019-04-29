<?php

namespace Extait\Articles\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscribersCollection;
use Magento\Framework\Exception\MailException;
use Extait\Articles\Model\ResourceModel\Article\CollectionFactory;

class ArticleMonthMailing {

    const XML_PATH_ARTICLE_MAIL_HEADER = 'articles/seo/article_mail_header';
    const TEMPLATE = "extait_articles_mailing";

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CollectionFactory
     */
    protected $articleCollection;

    /**
     * @var ArticleUrlRewriteGenerator
     */
    protected $articleUrlRewriteGenerator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var SubscribersCollection
     */
    protected $subscribersCollection;

    /**
     * ArticleMonthMailing constructor.
     * @param TransportBuilder $transportBuilder
     * @param CollectionFactory $articleCollection
     * @param ArticleUrlRewriteGenerator $articleUrlRewriteGenerator
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param SubscribersCollection $subscribersCollection
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        CollectionFactory $articleCollection,
        ArticleUrlRewriteGenerator $articleUrlRewriteGenerator,
        StoreManagerInterface $storeManager,
        Context $context,
        SubscribersCollection $subscribersCollection
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->articleCollection = $articleCollection;
        $this->articleUrlRewriteGenerator = $articleUrlRewriteGenerator;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->subscribersCollection = $subscribersCollection;
    }

    /**
     * @param $emailTemplateVariables
     * @param $receiverInfo
     * @param null $month
     * @throws NoSuchEntityException
     * @throws MailException
     */
    public function sendEmail($emailTemplateVariables, $receiverInfo, $month = null)
    {
        $senderName = $this->getSenderName();

        $senderEmail = $this->getSenderEmail();
        $sender = [
            'name' => $senderName,
            'email' => $senderEmail,
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars([
                'articles' => $emailTemplateVariables,
                'header' => $this->getMailHeader($month)
            ])
            ->setFrom($sender)
            ->addTo($receiverInfo)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * @param $month
     * @return array
     * @throws NoSuchEntityException
     */
    public function getArticlesList($month = null)
    {
        $list = [];

        $articles = $this->getArticlesFromPrevMonth($month);


        if ($articles != null) {
            foreach ($articles as $key => $article) {
                $id = $article->getId();
                $url = $this->articleUrlRewriteGenerator->getRewriteUrl($id);
                $list[$key]['url'] = $url;
                $list[$key]['title'] = $article->getTitle();

                $list[$key]['content'] = $this->cutContent($article->getContent());

                $list[$key]['created_at'] = $article->getCreatedAt();
            }
        }

        return $list;
    }

    /**
     * Cut content if it then more 255 symbols.
     *
     * @param $content
     * @return bool|string
     */
    protected function cutContent($content)
    {
        if (strlen($content) > 255) {
            $content = substr($content, 0, 255);
            $content = trim($content, ' ,./!?-');

            return $content = $content . "...";
        }

        return $content;
    }

    /**
     * @param $month
     * @return CollectionFactory
     */
    protected function getArticlesFromPrevMonth($month = null)
    {
        if ($month == null) {
            $month = date('m');
        }

        $month -= 1;
        $from = date("Y-m-01", mktime(0, 0, 0, $month, date('m') - 1));
        $to = date("Y-m-t", mktime(0, 0, 0, $month, date('m') - 1));

        return $this->articleCollection->create()
            ->addFieldToFilter('created_at', ['from' => $from, 'to' => $to])
            ->getItems();
    }

    /**
     * @return Context
     */
    protected function getSenderEmail()
    {
        return $this->context->getScopeConfig()->getValue('trans_email/ident_sales/email',
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return Context
     */
    protected function getSenderName()
    {
        return $this->context->getScopeConfig()->getValue('trans_email/ident_sales/name',
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $month
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getMailHeader($month = null)
    {
        $storeId = $this->storeManager->getStore()->getId();

        $header = $this->context->getScopeConfig()->getValue(
            self::XML_PATH_ARTICLE_MAIL_HEADER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $array = explode("|", $header);
        $array[1] = $this->getPreviousMonthInWord($month);

        $header = implode($array);

        return $header;
    }

    /**
     * @param $month
     * @return string
     */
    public function getPreviousMonthInWord($month=null)
    {
        if ($month == null) {
            $month = date('m');
        }

        $month -= 1;
        return date("F", mktime(0, 0, 0, $month, date('m') - 1));
    }

    /**
     * @return array
     */
    public function getSubscribersEmail()
    {
        $emails = [];

        $collection = $this->subscribersCollection->create()
            ->useOnlySubscribed();

        foreach ($collection as $item) {
            $emails[] = $item->getSubscriberEmail();
        }

        return $emails;
    }
}
