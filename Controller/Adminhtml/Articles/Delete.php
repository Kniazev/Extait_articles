<?php

namespace Extait\Articles\Controller\Adminhtml\Articles;

use Extait\Articles\Model\ArticleFactory;
use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var ArticleFactory
     */
    protected $articleFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param ArticleFactory $articleFactory
     */
    public function __construct(
        Action\Context $context,
        ArticleFactory $articleFactory
    ) {
        $this->articleFactory = $articleFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $collection = $this->articleFactory->create();
        $article = $collection->load($id);

        try {
            $article->delete();
        } catch (\Exception $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', ['_current' => true]);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', ['_current' => true]);
    }
}
