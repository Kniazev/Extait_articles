<?php

namespace Extait\Articles\Block\RelatedArticles;

use Magento\Framework\Convert\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Class RelatedProducts
 * @package Extait\Articles\Block\RelatedArticles
 * @api
 */
class RelatedProducts extends Template
{
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * RelatedProducts constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Image $imageHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject
     */
    public function getRelatedProducts()
    {
        return $this->getData('products');
    }

    /**
     * @param $product
     * @return string
     */
    public function getImageUrl($product)
    {
        $image_url = $this->imageHelper->init($product, 'product_page_image_small')
            ->setImageFile($product->getFile())->resize(200, 200)->getUrl();
        return $image_url;
    }

    /**
     * @param $product
     * @return float|string
     */
    public function getFormattedPrice($product)
    {
        $formattedPrice = $this->dataHelper->currency($product->getPrice(), true, false);
        return $formattedPrice;
    }
}
