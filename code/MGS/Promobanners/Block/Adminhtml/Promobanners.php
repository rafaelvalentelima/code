<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Promobanners\Block\Adminhtml;

class Promobanners extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_promobanners';
        $this->_blockGroup = 'MGS_Promobanners';
        $this->_headerText = __('Manage Banners');
        $this->_addButtonLabel = __('Add Banner');
        parent::_construct();
    }

}
