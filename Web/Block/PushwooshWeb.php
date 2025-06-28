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

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->helperData       = $helperData;
        $this->objectManager    = $objectManager;

        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->helperData;
    }
}
