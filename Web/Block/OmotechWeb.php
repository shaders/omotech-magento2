<?php

namespace Omotech\Web\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Omotech\Web\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;

class OmotechWeb extends Template
{
    /**
     * @var \Omotech\Web\Helper\Data
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
