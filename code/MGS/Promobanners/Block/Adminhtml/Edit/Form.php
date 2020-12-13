<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Promobanners\Block\Adminhtml\Edit;

/**
 * Sitemap edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
	/**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
	
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('promobanners__form');
        $this->setTitle(__('Promobanners Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('promobanners_promobanners');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
        );

        $fieldset = $form->addFieldset('add_promobanners_form', ['legend' => __('Banner Information')]);

        if ($model->getId()) {
            $fieldset->addField('promobanners_id', 'hidden', ['name' => 'promobanners_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Banner Title'),
                'name' => 'title',
                'required' => true
            ]
        );

		if($this->getRequest()->getParam('id')){
			$fieldset->addField(
				'filename',
				'file',
				[
					'label' => __('Image'),
					'name' => 'filename',
					'required' => false
				]
			);
		}
		else{
			$fieldset->addField(
				'filename',
				'file',
				[
					'label' => __('Image'),
					'name' => 'filename',
					'required' => true
				]
			);
		}
		
		
		
		$fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Content'),
				'style' => 'height:10em',
            ]
        );
		
		$fieldset->addField(
            'button',
            'text',
            [
                'label' => __('Button Text'),
                'name' => 'button',
                'required' => false
            ]
        );
		
		$fieldset->addField(
            'url',
            'text',
            [
                'label' => __('Url'),
                'name' => 'url',
                'required' => false
            ]
        );
		
		$fieldset->addField(
            'text_align',
            'select',
            [
                'label' => __('Text Align'),
                'name' => 'text_align',
                'required' => false,
                'options' => [
					''=> __('Default'),
					'top-left' => __('Top Left'),
					'top-middle' => __('Top Center'),
					'top-right' => __('Top Right'),
					'middle-left' => __('Middle Left'),
					'middle-center' => __('Middle Center'),
					'middle-right' => __('Middle Right'),
					'bottom-left' => __('Bottom Left'),
					'bottom-center' => __('Bottom Center'),
					'bottom-right' => __('Bottom Right')
				]
            ]
        );
		
		$fieldset->addField(
            'effect',
            'select',
            [
                'label' => __('Effect'),
                'name' => 'effect',
                'required' => false,
				'note' => __('Hover on preview image to see'),
                'options' => [
					''=> __('No Effect'),
					'zoom' => __('Effect 1'),
					'border-zoom' => __('Effect 2'),
					'flashed' => __('Effect 3'),
					'zoom-flashed' => __('Effect 4'),
					'shadow-corner' => __('Effect 5'),
					'zoom-shadow' => __('Effect 6'),
					'cup-border' => __('Effect 7'),
					'flashed-zoom' => __('Effect 8'),
					'zoom-out-shadow' => __('Effect 9'),
					'mist' => __('Effect 10'),
					'mist-text' => __('Effect 11'),
				]
            ]
        );
		
		$fieldset->addField(
            'custom_class',
            'text',
            [
                'label' => __('Custom Class'),
                'name' => 'custom_class',
                'required' => false
            ]
        );
		
		$fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'name' => 'status',
                'required' => false,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
