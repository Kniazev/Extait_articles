<?php

namespace Extait\Articles\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            /**
             * Create table 'link_article_products'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('link_article_products')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'article_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true,'default' => '0'],
                    'Article ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                    'Product ID'
                )
                ->addIndex(
                    $installer->getIdxName('link_article_products', ['article_id']),
                    ['article_id']
                )
                ->addIndex(
                    $installer->getIdxName('link_article_products', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'link_article_products',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'link_article_products',
                        'article_id',
                        'extait_articles',
                        'id'
                    ),
                    'article_id',
                    $installer->getTable('extait_articles'),
                    'id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $installer->getConnection()
                ->createTable($table);
        }
        $installer->endSetup();
    }
}
