<?php

namespace Extait\Articles\Block\Adminhtml\Article\Edit;

use \Magento\Backend\Block\Widget\Context;
use \Magento\Framework\Registry;

/**
 * Class GenericButton
 * @package Extait\Articles\Block\Adminhtml\Article\Edit
 */
class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * Return the synonyms group Id.
     *
     * @return int|null
     */
    public function getId()
    {
        $contact = $this->registry->registry('article');
        return $contact ? $contact->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
