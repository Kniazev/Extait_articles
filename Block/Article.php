<?php

namespace Extait\Articles\Block;

use Magento\Framework\View\Element\Template\Context;
use Extait\Articles\Helper\DateHelper;
use Magento\Framework\View\Element\Template;

/**
 * Class Article
 * @package Extait\Articles\Block
 * @api
 */
class Article extends Template
{
    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * Article constructor.
     * @param Context $context
     * @param DateHelper $dateHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateHelper $dateHelper,
        array $data = []
    ) {
        $this->dateHelper = $dateHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return Template|void
     */
    protected function _prepareLayout()
    {
        $article = $this->getArticle();
        $this->setDate($this->dateHelper->convertDate($article['published_at']));
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->getData('article');
    }
}
