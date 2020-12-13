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

namespace ImaginationMedia\Correios\Block\Adminhtml\Cotacoes\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use ImaginationMedia\Correios\Model\CotacoesFactory;

class SaveButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CotacoesFactory
     */
    protected $cotacoes;

    /**
     * SaveButton constructor.
     * @param Context $context
     * @param CotacoesFactory $cotacoes
     */
    public function __construct(
        Context $context,
        CotacoesFactory $cotacoes
    ) {
        $this->context = $context;
        $this->cotacoes = $cotacoes;
    }

    /**
     * @return null
     */
    public function getPageId()
    {
        try {
            return $this->cotacoes->load(
                $this->context->getRequest()->getParam('cotacoes_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Track'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}