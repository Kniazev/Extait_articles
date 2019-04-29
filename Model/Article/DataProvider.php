<?php

namespace Extait\Articles\Model\Article;

use Extait\Articles\Model\ResourceModel\Article\Collection;
use Extait\Articles\Model\ResourceModel\Article\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var PoolInterface
     */
    protected $poolInterface;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $poolInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $poolInterface,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->poolInterface = $poolInterface;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->poolInterface->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        foreach ($items as $article) {
            $this->data[$article->getId()] = $article->getData();
        }

        foreach ($this->poolInterface->getModifiersInstances() as $modifier) {
                $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }
}
