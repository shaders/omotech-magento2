<?php

namespace Pushwoosh\AbandonedCart\Block\System\Config;

class Widget extends \Magento\Config\Block\System\Config\Form\Fieldset
{

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild('cart_sync_status', SyncAbandonedCartData::class);

        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getChildHtml('cart_sync_status');
    }
}
