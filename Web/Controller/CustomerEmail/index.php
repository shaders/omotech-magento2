<?php

namespace Pushwoosh\Web\Controller\CustomerEmail;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;

class Index extends Action
{
    protected $customerSession;
    public function __construct(
        Context $context,
        Session $customerSession
    )
    {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $email = null;
        if ($this->customerSession->isLoggedIn()) {
            $email = $this->customerSession->getCustomer()->getEmail();
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['email' => $email]);
    }
}