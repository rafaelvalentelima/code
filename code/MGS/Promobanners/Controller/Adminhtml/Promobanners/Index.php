<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Promobanners\Controller\Adminhtml\Promobanners;

use Magento\Backend\App\Action;

class Index extends \MGS\Promobanners\Controller\Adminhtml\Promobanners
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Banners'));
        $this->_view->renderLayout();
    }
}
