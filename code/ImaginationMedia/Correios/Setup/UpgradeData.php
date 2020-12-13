<?php

namespace ImaginationMedia\Correios\Setup;

use Magento\Catalog\Model\Product as Product;
use Magento\Eav\Setup\EavSetup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var EavSetup
     */
    private EavSetup $eavSetup;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetup $eavSetup
     */
    public function __construct(EavSetup $eavSetup)
    {
        $this->eavSetup = $eavSetup;
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.6', '<=')) {
            $this->eavSetup->updateAttribute(Product::ENTITY, 'correios_width', 'is_filterable', false);
            $this->eavSetup->updateAttribute(Product::ENTITY, 'correios_height', 'is_filterable', false);
            $this->eavSetup->updateAttribute(Product::ENTITY, 'correios_depth', 'is_filterable', false);
        }

        $setup->endSetup();
    }
}
