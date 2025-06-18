<?php

namespace Pushwoosh\AbandonedCart\Cron;

use Pushwoosh\AbandonedCart\Helper\Data as AbandonedCartHelper;
use Pushwoosh\AbandonedCart\Model\AbandonedCartSendData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AbandonedCartSync
{
    /**
     * @var AbandonedCartHelper
     */
    protected $abandonedCartHelper;

    /**
     * @var AbandonedCartSendData
     */
    protected $abandonedCartSendData;

    /**
     * Abandoned cart sync constructor.
     * @param AbandonedCartHelper $abandonedCartHelper
     * @param AbandonedCartSendData $abandonedCartSendData
     */
    public function __construct(
        AbandonedCartHelper $abandonedCartHelper,
        AbandonedCartSendData $abandonedCartSendData
    ) {
        $this->abandonedCartHelper = $abandonedCartHelper;
        $this->abandonedCartSendData = $abandonedCartSendData;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->abandonedCartHelper->isAbandonedCartSyncingEnabled()) {
            return;
        }

        try {
            $this->abandonedCartSendData->sendAbandonedCartData();
        } catch (\Exception $e) {

        }
    }
}
