<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Promobanners\Block\Widget;

/**
 * Cms Static Block Widget
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Banner extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
    }

    public function getModel(){
		return $this->_objectManager->create('MGS\Promobanners\Model\Promobanners');
	}
	
	public function getBannerById($id){
		$banner = $this->getModel()->load($id);
		return $banner;
	}
	
	public function getBannerImageUrl($banner){
		$bannerUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'promobanners/'.$banner->getFilename();
		return $bannerUrl;
	}
	
	public function getCustomClass($banner){
		$class = '';
		if($banner->getCustomClass()!=''){
			$class .= ' '.$banner->getCustomClass();
		}
		if($banner->getEffect()!=''){
			$class .= ' '.$banner->getEffect();
		}
		return $class;
	}
}
