<?php

/**
 * Correios
 *
 * Correios Shipping Method for Magento 2.
 *
 * @package ImaginationMedia\Correios
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @author Douglas Ianitsky <ianitsky@gmail.com>
 * @copyright Copyright (c) 2017-2019 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace ImaginationMedia\Correios\Model\Config\Source;

use ImaginationMedia\Correios\Helper\Data as Helper;
use Magento\Framework\Option\ArrayInterface;

class PostingMethods implements ArrayInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * PostingMethods constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $methods = $this->helper->getMethodsData();

        $return = [];
        if (is_array($methods)) {
            foreach ($methods as $method) {
                $return[] = [
                    'value' => $method['code'],
                    'label' => $method['name'] . ' (' . $method['code'] . ')'
                ];
            }
        }

        return $return;
    }
}
