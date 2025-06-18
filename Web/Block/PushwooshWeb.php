<?php

namespace Pushwoosh\Web\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pushwoosh\Web\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;

class PushwooshWeb extends Template
{
    /**
     * @var \Pushwoosh\Web\Helper\Data
     */
    protected $helperData;
    protected $objectFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected $_customerSession;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->helperData       = $helperData;
        $this->objectManager    = $objectManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;

        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->helperData;
    }

    public function getOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        return $order;
    }

    public function cleanUpValue($str)
    {
        $str = trim(htmlspecialchars(strip_tags($str)));

        return $str;

    }

    public function getOrderId()
    {
        return $this->getOrder()->getIncrementId();
    }

    public function getGrandTotal()
    {
        return $this->getOrder()->getGrandTotal();
    }


    public function getShippingAmount()
    {
        return $this->getOrder()->getShippingAmount();
    }

    public function getTaxAmount()
    {
        return $this->getOrder()->getTaxAmount();
    }

    public function getOrderStoreName()
    {
        $name = $this->cleanUpValue($this->getOrder()->getStoreName());
        $name = preg_replace("/\r|\n/", "", $name);
        return $name;
    }

    public function getOrderItems()
    {
        /** @Magento/Sales/Model/Order/Items */
        return $this->getOrder()->getAllItems();
    }

    public function getCustomerEmail(): ?string
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomer()->getEmail();
        }

        return null;
    }
}
