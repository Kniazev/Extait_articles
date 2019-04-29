<?php

namespace Extait\Articles\Test\Integration\Controller\Adminhtml\Articles;

/**
 * @magentoAppArea adminhtml
 */
class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public static function loadFixture()
    {
        include __DIR__ . '/../../../_files/articles.php';
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     */
    public function testArticleDuplicateError()
    {
        $params = [
            'title' => 'Test',
            'author' => 'test',
            'content' => 'content'
        ];

        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/articles/articles/save');

        $this->assertSessionMessages(
            $this->equalTo(['Article with this title already existing.'])
        );
    }

    public function testSaveArticle()
    {
        $params = [
            'title' => 'Save Test',
            'author' => 'test',
            'content' => 'content'
        ];

        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/articles/articles/save');

        $this->assertSessionMessages(
            $this->equalTo(['Article was saved.'])
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     */
    public function testArticleEdit()
    {
        $params = [
            'id' => 2,
            'title' => 'Edit Test',
            'author' => 'test',
            'content' => 'content'
        ];

        $this->getRequest()->setPostValue($params);

        $this->dispatch('backend/articles/articles/save');

        $article = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Extait\Articles\Model\Article');
        $article->load(2);

        $this->assertEquals('Edit Test', $article->getTitle());
    }
}
