<?php
namespace Pushwoosh\AbandonedCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Pushwoosh\AbandonedCart\Model\Config\CronConfig;

class Data extends AbstractHelper
{
    const PUSHWOOSH_ABANDONED_CART_SYNC = "pushwoosh/abandoned_cart/sync";
    const ABANDONED_CART_NUMBER_OF_ABANDONED_CART = "pushwoosh/abandoned_cart/number_of_abandoned_cart";
    const ABANDONED_CART_MIN_INACTIVE_TIME = "pushwoosh/abandoned_cart/min_inactive_time";
    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isAbandonedCartSyncingEnabled($scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::PUSHWOOSH_ABANDONED_CART_SYNC,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return mixed
     */
    public function getCronTime($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            CronConfig::CRON_MODEL_PATH,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return mixed
     */
    public function getNumberOfAbandonedCart($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::ABANDONED_CART_NUMBER_OF_ABANDONED_CART,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return mixed
     */
    public function getMinInactiveTime($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::ABANDONED_CART_MIN_INACTIVE_TIME,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

}
