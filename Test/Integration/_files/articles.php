<?php

 /** @var $article \Extait\Articles\Model\Article */
$article = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Extait\Articles\Model\Article');
$article->isObjectNew(true);

$article
    ->setTitle('Test')
    ->setAuthor('Test author')
    ->setContent('content content content vcontent content content')
    ->setCreatedAt('2019-01-03')
    ->setUpdateAt('2019-01-02')
    ->setPublishedAt('2019-01-09')
    ->setPublished(true)
    ->save();

$article
    ->setTitle('Test')
    ->setAuthor('Test author')
    ->setContent('content content content vcontent content content')
    ->setCreatedAt('2019-01-03')
    ->setUpdateAt('2019-01-02')
    ->setPublishedAt('2019-01-09')
    ->setPublished(true)
    ->save();

