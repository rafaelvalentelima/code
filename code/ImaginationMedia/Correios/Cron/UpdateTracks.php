<?php

/**
 * Correios
 *
 * Correios Shipping Method for Magento 2.
 *
 * @package ImaginationMedia\Correios
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2017 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace ImaginationMedia\Correios\Cron;

use ImaginationMedia\Correios\Helper\Data as CorreiosHelper;

class UpdateTracks
{
    /**
     * @var CorreiosHelper
     */
    protected $helper;

    /**
     * UpdateTracks constructor.
     * @param CorreiosHelper $helper
     */
    public function __construct(CorreiosHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->helper->logMessage("Cron job updateTracks executed.");
        $this->helper->updateOfflineTracks();
        return $this;
    }
}
