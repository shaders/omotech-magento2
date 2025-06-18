<?php
namespace Omotech\Order\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const OMOTECH_ORDER_STATUS = "omotech/order_sync/order_sync_enable";
    const OMOTECH_ORDER_SYNC_NUM = "omotech/order_sync/order_sync_num";
    const OMOTECH_ORDER_SYNC_REAL_TIME = "omotech/order_sync/order_sync_real_time";

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
            self::OMOTECH_ORDER_STATUS,
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
            self::OMOTECH_ORDER_SYNC_REAL_TIME,
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
            self::OMOTECH_ORDER_SYNC_NUM,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }
}
