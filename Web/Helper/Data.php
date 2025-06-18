<?php
namespace Pushwoosh\Web\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Pushwoosh\Core\Helper\Data as CoreHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Pushwoosh\Core\Helper\Data
     */
    protected $coreHelper;

    const PUSHWOOSH_WEB_ENABLED = "pushwoosh/web/enabled";

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
        ConfigInterface $configInterface,
        CoreHelper $coreHelper
    ) {
        $this->configInterface = $configInterface;
        $this->coreHelper = $coreHelper;
        parent::__construct($context);
    }

    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isEnabled($scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PUSHWOOSH_WEB_ENABLED,
            ScopeInterface::SCOPE_STORES,
            $scopeCode
        );
    }

    /**
     * @param null $scopeCode
     * @return string|null
     */
    public function getApplicationCode($scopeCode = null)
    {
        return $this->coreHelper->getAppCode($scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string|null
     */
    public function getApiToken($scopeCode = null)
    {
        return $this->coreHelper->getApiKey($scopeCode);
    }
}
