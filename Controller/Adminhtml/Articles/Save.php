<?php

namespace Extait\Articles\Controller\Adminhtml\Articles;

use Extait\Articles\Model\ArticleFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\App\Action;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Extait_Articles::save';

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;
    /**
     * @var ArticleFactory
     */
    protected $articleFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param ArticleFactory $articleFactory
     * @param TimezoneInterface $timezone
     * @param UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        Action\Context $context,
        ArticleFactory $articleFactory,
        TimezoneInterface $timezone,
        UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->articleFactory = $articleFactory;
        $this->timezone = $timezone;
        $this->urlRewriteFactory = $urlRewriteFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            $this->editArticle($id, $data);
        } else {
            try {
                $title = $this->getRequest()->getParam('title');

                if ($this->isDuplicateTitle($title)) {
                    $this->messageManager->addErrorMessage('Article with this title already existing.');

                    return $resultRedirect->setPath('*/*/add');
                }

                $this->saveArticle($data);
                $this->messageManager->addSuccessMessage('Article was saved.');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * @param $title
     * @return int
     */
    protected function isDuplicateTitle($title)
    {
        $collection = $this->articleFactory->create()
            ->getCollection()
            ->addFieldToFilter('title', ["in" => $title]);

        return $collection->getSize();
    }

    /**
     * @param $id
     * @param $data
     * @throws \Exception
     */
    protected function editArticle($id, $data)
    {
        $article = $this->articleFactory->create();

        $article = $article->load($id);
        $article->setData($data)
            ->save();
    }

    /**
     * @param $data
     * @throws \Exception
     */
    protected function saveArticle($data)
    {
        $article = $this->articleFactory->create();

        $article->setData($data)
            ->save();
    }
}
