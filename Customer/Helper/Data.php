<?php
namespace Omotech\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Omotech\Customer\Model\Config\CronConfig;
use \Magento\Framework\App\Config\ConfigResource\ConfigInterface;
class Data extends AbstractHelper
{
    const OMOTECH_CUSTOMER_SYNC = "omotech/customer/sync";
    const OMOTECH_CUSTOMER_NUMBER_OF_CUSTOMERS = "omotech/customer/number_of_customers";
    const OMOTECH_CUSTOMER_UPDATE_LAST_SYNC = "omotech/customer/last_customers_updated";
    const OMOTECH_CUSTOMER_MAP_CUSTOM_FIELDS = "omotech/customer/map_custom_fields";

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configInterface;
    /**
     * Data constructor.
     * @param Context $context
     */


    public function __construct(
        Context $context,
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
        parent::__construct($context);
    }

    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isCustomerSyncingEnabled($scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::OMOTECH_CUSTOMER_SYNC,
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
    public function getNumberOfCustomers($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::OMOTECH_CUSTOMER_NUMBER_OF_CUSTOMERS,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    public function getLastCustomerUpdateSync($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::OMOTECH_CUSTOMER_UPDATE_LAST_SYNC,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    public function getMapCustomFields($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::OMOTECH_CUSTOMER_MAP_CUSTOM_FIELDS,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    public function setLastCustomerUpdateSync($date, $scopeCode = null)
    {
        $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->configInterface->saveConfig(self::OMOTECH_CUSTOMER_UPDATE_LAST_SYNC, $date, $scope);
    }
}
