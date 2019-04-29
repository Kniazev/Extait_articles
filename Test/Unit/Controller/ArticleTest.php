<?php

namespace Extait\Articles\Test\Unit\Controller;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * @var \Extait\Articles\Model\ArticleFactory
     */
    protected $articleFactoryMock;

    /**
     * @var PageFactory
     */
    protected $pageFactoryMock;

    /**
     * @var ProductFactory
     */
    protected $productFactoryMock;

    protected $modelLinkMock;

    protected $modelProductMock;

    /**
     * @var LinkFactory
     */
    protected $linkFactoryMock;

    protected $articleController;

    protected $productCollectionMock;

    public function setUp()
    {
        $this->articleFactoryMock = $this->getMockBuilder(\Extait\Articles\Model\ArticleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->productFactoryMock = $this->getMockBuilder(\Magento\Catalog\Model\ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->modelProductMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->productCollectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->modelLinkMock = $this->getMockBuilder(\Extait\Articles\Model\ResourceModel\Article\Link::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkFactoryMock = $this->getMockBuilder(\Extait\Articles\Model\ResourceModel\Article\LinkFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->articleController = $objectManagerHelper->getObject(
            \Extait\Articles\Controller\Index\Article::class,
            [
                'articleFactory' => $this->articleFactoryMock,
                'pageFactory' => $this->pageFactoryMock,
                'productFactory' => $this->productFactoryMock,
                'linkFactory' => $this->linkFactoryMock
            ]
        );
    }

    public function testGetRelatedProductsNull()
    {
        $this->linkFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->modelLinkMock);
        $this->modelLinkMock->expects($this->once())->method('getProductsIdsByArticle')
            ->willReturn([]);

        $collection = $this->articleController->getRelatedProducts(94);
        $this->assertNull($collection);
    }

    public function testGetRelatedProducts()
    {
        $this->linkFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->modelLinkMock);
        $this->modelLinkMock->expects($this->once())->method('getProductsIdsByArticle')
            ->willReturn([2,4,6]);
        $this->productFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->modelProductMock);
        $this->modelProductMock->expects($this->once())->method('getCollection')->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())->method('addFieldToFilter')->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())->method('addAttributeToSelect')->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())->method('getItems')->willReturn([2,4,6]);
        $collection = $this->articleController->getRelatedProducts(94);
        $this->assertEquals([2,4,6], $collection);
    }
}
