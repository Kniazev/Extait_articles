<?php

namespace Extait\Articles\Test\Integration\Controller\Index;

class ArticleTest extends \Magento\TestFramework\TestCase\AbstractController
{

    public static function loadFixture()
    {
        include __DIR__ . '/../../_files/articles.php';
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoAppArea frontend
     */
    public function testArticlesDisplayCorrect()
    {
        $this->dispatch('extait_articles/index/article/id/1');

        $this->assertContains('Test', $this->getResponse()->getBody());
        $this->assertNotContains('Test2', $this->getResponse()->getBody());
    }
}
