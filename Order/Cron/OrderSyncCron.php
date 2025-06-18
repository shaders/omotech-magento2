<?php

namespace Omotech\Order\Cron;

use Omotech\Core\Helper\Curl;
use Omotech\Order\Block\Adminhtml\System\Config\OrderSyncStatus;
use Omotech\Order\Helper\Data as OmotechOrderHelper;
use Omotech\Core\Logger\Logger as OmotechLogger;
use Omotech\Order\Model\OrderData\OrderDataSend;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class OrderSyncCron
{
    /**
     * @var OmotechOrderHelper
     */
    private $omotechHelper;

    /**
     * @var OrderDataSend
     */
    protected $orderDataSend;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var OmotechLogger
     */
    private $logger;

    /**
     * OrderSyncCron constructor.
     * @param OrderDataSend $orderDataSend
     * @param CollectionFactory $orderCollectionFactory
     * @param OmotechOrderHelper $omotechHelper
     * @param State $state
     * @param Curl $curl
     * @param CartRepositoryInterface $quoteRepository
     * @param OmotechLogger $logger
     */
    public function __construct(
        OrderDataSend $orderDataSend,
        CollectionFactory $orderCollectionFactory,
        OmotechOrderHelper $omotechHelper,
        State $state,
        Curl $curl,
        CartRepositoryInterface $quoteRepository,
        OmotechLogger $logger
    ) {
        $this->orderDataSend = $orderDataSend;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->omotechHelper = $omotechHelper;
        $this->state = $state;
        $this->curl = $curl;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * @throws NoSuchEntityException|GuzzleException
     */
    public function execute(): void
    {
        try {
            $isEnabled = $this->omotechHelper->isOrderSyncEnabled();
            if (!$isEnabled) {
                return;
            }

            $OrderSyncNum = $this->omotechHelper->getOrderSyncNum();
            $orderCollection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter(
                    OrderSyncStatus::PW_SYNC_STATUS,
                    ['eq' => 0]
                )
                ->setPageSize($OrderSyncNum)->setOrder('main_table.entity_id',"desc");

            foreach ($orderCollection as $order) {
                try {
                    $this->orderDataSend->orderDataSend($order);
                } catch (NoSuchEntityException|GuzzleException $e) {
                    $this->logger->error('MODULE Order: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('MODULE Order: ' . $e->getMessage());
        }
    }
}
