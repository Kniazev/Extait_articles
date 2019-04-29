<?php

namespace Extait\Articles\Model\Article;

use Magento\Framework\DataObject\IdentityInterface;

class Link extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'link';

    protected $_cacheTag = 'link';

    protected $_eventPrefix = 'link';

    protected function _construct()
    {
        $this->_init(Link::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
