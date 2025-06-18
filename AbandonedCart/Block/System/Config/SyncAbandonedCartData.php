<?php
declare(strict_types=1);

namespace Omotech\AbandonedCart\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteResourceCollectionFactory;
use Omotech\AbandonedCart\Model\Config\CronConfig;

class SyncAbandonedCartData extends \Magento\Backend\Block\Template
{
    public const PW_SYNC_STATUS = 'pw_sync_status';

    /**
     * @var string
     */
    protected $_template = 'Omotech_AbandonedCart::system/config/sync_abandoned_cart_data.phtml';

    /**
     * @var QuoteResourceCollectionFactory
     */
    protected $quoteResourceCollectionFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param QuoteResourceCollectionFactory $quoteResourceCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        QuoteResourceCollectionFactory $quoteResourceCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteResourceCollectionFactory = $quoteResourceCollectionFactory;
    }

    /**
     * Get abandoned cart collection
     *
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAbandonedCartCollection()
    {
        return $this->quoteResourceCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'main_table.is_active',
                '1'
            );
    }

    /**
     * Get sync abandoned cart
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSyncAbandonedCart()
    {
        $collection = $this->getAbandonedCartCollection()->addFieldToFilter(
            self::PW_SYNC_STATUS,
            [
                ['eq' => CronConfig::SYNCED]
            ]
        );

        return $collection->getSize();
    }

    /**
     * Get total abandoned cart
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTotalAbandonedCart()
    {
        $collection = $this->getAbandonedCartCollection()->addFieldToFilter(
            self::PW_SYNC_STATUS,
            [
                ['eq' => CronConfig::SYNCED],
                ['eq' => CronConfig::NOT_SYNCED],
                ['eq' => CronConfig::FAIL_SYNCED]
            ]
        )->addFieldToFilter('items_count',['gt' => 0]);

        return $collection->getSize();
    }

    /**
     * Get not sync abandoned cart
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotSyncAbandonedCart()
    {
        $collection = $this->getAbandonedCartCollection()->addFieldToFilter(
            self::PW_SYNC_STATUS,
            [
                ['eq' => CronConfig::NOT_SYNCED]
            ]
        )->addFieldToFilter('items_count',['gt' => 0]);

        return $collection->getSize();
    }

    /**
     * Get failed sync
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFailedSync()
    {
        $collection = $this->getAbandonedCartCollection()->addFieldToFilter(
            self::PW_SYNC_STATUS,
            [
                ['eq' => CronConfig::FAIL_SYNCED]
            ]
        )->addFieldToFilter('items_count',['gt' => 0]);

        return $collection->getSize();
    }
}
