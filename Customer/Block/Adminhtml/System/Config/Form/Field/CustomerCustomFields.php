<?php

namespace Omotech\Customer\Block\Adminhtml\System\Config\Form\Field;

class CustomerCustomFields extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    private $customerOptions;
    private $pwOptions;

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row): void
    {
       $options = [];

        $row->setData('option_extra_attrs', $options);
    }


    protected function _prepareToRender()
    {
        $this->addColumn(
            'customer_field_id',
            [   'label' => __('Magento'),
                'renderer' => $this->getCustomerField()
            ]
        );
        $this->addColumn(
            'pw_customer_field_id',
            ['label' => __('Omotech'),
                'renderer' => $this->getPwTags()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    private function getCustomerField(){
        if (!$this->customerOptions) {
            $this->customerOptions = $this->getLayout()->createBlock(
                CustomerOptionColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->customerOptions;
    }

    private function getPwTags(){
        if (!$this->pwOptions) {
            $this->pwOptions = $this->getLayout()->createBlock(
                PwOptionColumn::class,
                            '',
                            ['data' => ['is_render_to_js_template' => true]]
                        );
        }
        return $this->pwOptions;

    }
}
