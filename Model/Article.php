<?php

namespace Extait\Articles\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;

class Article extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'extait_articles';

    protected $_cacheTag = 'extait_articles';

    protected $_eventPrefix = 'extait_articles';

    protected function _construct()
    {
        $this->_init(\Extait\Articles\Model\ResourceModel\Article::class);
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
