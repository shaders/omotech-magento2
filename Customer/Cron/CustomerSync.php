<?php

namespace Omotech\Customer\Cron;

use Omotech\Customer\Model\Customer;


class CustomerSync
{

    /**
     * @var Customer
     */
    protected $customer;


    /**
     * CustomerSync constructor.
     * @param Customer $customer
     */
    public function __construct(
        Customer $customer
    )
    {
        $this->customer = $customer;
    }

    public function execute()
    {
        $this->customer->syncCustomers();
    }

}
