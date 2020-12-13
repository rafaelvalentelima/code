<?php
/**
 * JulioReis_CorreiosFollowup
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  JulioReis
 * @package   JulioReis_CorreiosFollowup
 *
 * @copyright Copyright (c) 2018 Julio Reis (www.rapidets.com.br)
 *
 * @author    Julio Reis <julioreis.si@gmail.com>
 */

namespace JulioReis\CorreiosFollowup\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    use SetupHelper;

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;

        $this->setup()->startSetup();

        $this->installTrackingQueueTable();

        $this->setup()->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    private function installTrackingQueueTable()
    {
        $tableName = $this->getTable('julioreis_correiosfollowup_tracking_queue');

        /** Drop the table first. */
        $this->getConnection()->dropTable($tableName);

        /** @var Table $table */
        $table = $this->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('shipment_track_id', Table::TYPE_INTEGER, 10, [
                'nullable' => false,
                'unsigned' => true,
            ], 'Order Entity ID')
            ->addColumn('correios_status', Table::TYPE_TEXT, 2, [
                'nullable' => true,
                'default' => null,
            ])
            ->addColumn('statuses_qty', Table::TYPE_INTEGER, 3, [
                'nullable' => false,
                'unsigned' => true,
            ], 'Statuses Qty')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ]);

        $this->addTableForeignKey($table, 'shipment_track_id', 'sales_shipment_track', 'entity_id');
        $this->addTableIndex($table, ['shipment_track_id'], AdapterInterface::INDEX_TYPE_UNIQUE);

        $this->getConnection()->createTable($table);

        return $this;
    }
}