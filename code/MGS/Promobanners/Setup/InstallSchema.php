<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Promobanners\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'promobanners'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('promobanners')
        )->addColumn(
            'promobanners_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Promobanners Id'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            [],
            'Title'
        )->addColumn(
            'filename',
            Table::TYPE_TEXT,
            255,
            [],
            'Filename'
        )->addColumn(
            'url',
            Table::TYPE_TEXT,
            255,
            [],
            'Url'
        )->addColumn(
            'button',
            Table::TYPE_TEXT,
            255,
            [],
            'Button'
        )->addColumn(
            'text_align',
            Table::TYPE_TEXT,
            255,
            [],
            'Text Align'
        )->addColumn(
            'content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Content'
        )->addColumn(
            'effect',
            Table::TYPE_TEXT,
            255,
            [],
            'Effect'
        )->addColumn(
            'custom_class',
            Table::TYPE_TEXT,
            255,
            [],
            'Custom Class'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Is Active'
        )
		->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Creation Time'
        )
		->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Update Time'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
