<?php
namespace Omotech\Order\Observer;

use Omotech\Core\Helper\Curl;
use Omotech\Order\Helper\Data as OmotechOrderHelper;
use Omotech\Core\Logger\Logger as OmotechLogger;
use Omotech\Order\Model\OrderData\OrderDataSend;
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
     * @var OmotechOrderHelper
     */
    private $omotechHelper;

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
     * @var OmotechLogger
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
     * @param OmotechOrderHelper $omotechHelper
     * @param OrderModel $orderModel
     * @param CartRepositoryInterface $quoteRepository
     * @param OmotechLogger $logger
     */
    public function __construct(
        OrderDataSend           $orderDataSend,
        Curl                    $curl,
        OmotechOrderHelper    $omotechHelper,
        OrderModel              $orderModel,
        CartRepositoryInterface $quoteRepository,
        OmotechLogger         $logger
    ) {
        $this->orderDataSend = $orderDataSend;
        $this->curl = $curl;
        $this->omotechHelper = $omotechHelper;
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
        $synInRealTime = $this->omotechHelper->isOrderSyncInRealTime();
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
