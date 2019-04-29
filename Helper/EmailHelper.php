<?php

namespace Extait\Articles\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;

class EmailHelper extends AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'articles/seo/template_notification';

    /**
     * @var Context
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Email constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $context;
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @return mixed
     */
    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $emailTemplateVariables
     * @param $receiverInfo
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendEmail($emailTemplateVariables, $receiverInfo)
    {
        $senderName = 'test';

        $senderEmail = $this->getSenderEmail();

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail,
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier('extait_articles_email_template_notification')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars([
                'articles' => $emailTemplateVariables,
                'productName' => $emailTemplateVariables['productName']
            ])
            ->setFrom($sender)
            ->addTo($receiverInfo)
            ->getTransport();
        $transport->sendMessage();
    }
}
