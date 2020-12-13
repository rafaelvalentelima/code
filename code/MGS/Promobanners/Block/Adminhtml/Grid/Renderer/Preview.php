<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace MGS\Promobanners\Block\Adminhtml\Grid\Renderer;

class Preview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		/* $html = $this->getLayout()->createBlock('MGS\Promobanners\Block\Promobanners')->setBannerId($row->getId())->setTemplate('banner.phtml')->toHtml();
		return $html; */
		$html = '<div style="max-width:450px"><div class="promobanner'.$this->getCustomClass($row).'">';
		$html .= '<a><img alt="" src="'.$this->getBannerImageUrl($row).'" class="img-responsive" /></a>';
		
		if(($row->getContent() != '') || ($row->getButton() != '')){
			$html .= '<div class="text '.$row->getTextAlign().'">';
			
			if($row->getContent() != ''){
				$html .= '<div class="banner-text">'.$row->getContent().'</div>';
			}
			if($row->getButton() != ''){
				$html .= '<span class="banner-button"><button class="btn btn-default btn-promo-banner">'.$row->getButton().'</button></span>';
			}
			$html .= '</div>';
		}

		$html .= '</div></div>';
		return $html;
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
