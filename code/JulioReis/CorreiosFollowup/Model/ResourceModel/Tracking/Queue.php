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

namespace JulioReis\CorreiosFollowup\Model\ResourceModel\Tracking;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    public function _construct()
    {
        $this->_init('julioreis_correiosfollowup_tracking_queue', 'id');
    }
}
