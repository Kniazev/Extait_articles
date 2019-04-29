<?php

namespace Extait\Articles\Cron;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Extait\Articles\Model\ArticleMonthMailing;

/**
 * Send mails to subscribe users.
 * Class Mailing
 * @package Extait\Articles\Cron
 */
class Mailing
{
    const XML_PATH_ARTICLE_MAIL_HEADER = 'articles/seo/article_mail_header';

    /**
     * @var ArticleMonthMailing
     */
    protected $mailing;

    /**
     * Mailing constructor.
     * @param ArticleMonthMailing $mailing
     */
    public function __construct(
        ArticleMonthMailing $mailing
    ) {
        $this->mailing = $mailing;
    }

    /**
     * @throws NoSuchEntityException
     * @throws MailException
     */
    public function execute()
    {
        $articles['list'] = $this->mailing->getArticlesList();

        if ($articles != null) {
            $receivers = $this->mailing->getSubscribersEmail();

            $this->mailing->sendEmail($articles, $receivers);
        }
    }
}
