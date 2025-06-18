<?php
namespace Pushwoosh\Order\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const PUSHWOOSH_ORDER_STATUS = "pushwoosh/order_sync/order_sync_enable";
    const PUSHWOOSH_ORDER_SYNC_NUM = "pushwoosh/order_sync/order_sync_num";
    const PUSHWOOSH_ORDER_SYNC_REAL_TIME = "pushwoosh/order_sync/order_sync_real_time";

    /**
     * @var \Magento\Framework\App\State *
     */
    private $state;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct($context);
        $this->state = $state;
    }

    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isOrderSyncEnabled($scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PUSHWOOSH_ORDER_STATUS,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isOrderSyncInRealTime($scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PUSHWOOSH_ORDER_SYNC_REAL_TIME,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return mixed
     */
    public function getOrderSyncNum($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::PUSHWOOSH_ORDER_SYNC_NUM,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }
}
