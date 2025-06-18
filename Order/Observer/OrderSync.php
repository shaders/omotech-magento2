<?php
namespace Pushwoosh\Order\Observer;

use Pushwoosh\Core\Helper\Curl;
use Pushwoosh\Order\Helper\Data as PushwooshOrderHelper;
use Pushwoosh\Core\Logger\Logger as PushwooshLogger;
use Pushwoosh\Order\Model\OrderData\OrderDataSend;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;

class OrderSync implements ObserverInterface
{
    public const DELETE_METHOD = "DELETE";
    public const URL_ENDPOINT = "ecomOrders/";

    /**
     * @var PushwooshOrderHelper
     */
    private $pushwooshHelper;

    /**
     * @var OrderDataSend
     */
    protected $orderDataSend;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var PushwooshLogger
     */
    private $logger;
    /**
     * @var OrderModel
     */
    private $orderModel;

    /**
     * OrderSync constructor.
     * @param OrderDataSend $orderDataSend
     * @param Curl $curl
     * @param PushwooshOrderHelper $pushwooshHelper
     * @param OrderModel $orderModel
     * @param CartRepositoryInterface $quoteRepository
     * @param PushwooshLogger $logger
     */
    public function __construct(
        OrderDataSend           $orderDataSend,
        Curl                    $curl,
        PushwooshOrderHelper    $pushwooshHelper,
        OrderModel              $orderModel,
        CartRepositoryInterface $quoteRepository,
        PushwooshLogger         $logger
    ) {
        $this->orderDataSend = $orderDataSend;
        $this->curl = $curl;
        $this->pushwooshHelper = $pushwooshHelper;
        $this->orderModel = $orderModel;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $synInRealTime = $this->pushwooshHelper->isOrderSyncInRealTime();
        if (!$synInRealTime) {
            return;
        }

        $orderIds = $observer->getEvent()->getOrderIds();

        foreach ($orderIds as $orderId) {
            $orderData = $this->orderModel->load($orderId);
            try {
                $this->orderDataSend->orderDataSend($orderData);
            } catch (\Exception $e) {}
        }
    }
}
