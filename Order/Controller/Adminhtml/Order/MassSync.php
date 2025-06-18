<?php

namespace Omotech\Order\Controller\Adminhtml\Order;

use Omotech\Order\Block\Adminhtml\System\Config\OrderSyncStatus;
use Omotech\Order\Model\OrderData\OrderDataSend;
use Omotech\Core\Logger\Logger as OmotechLogger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Quote\Api\CartRepositoryInterface;
use Omotech\Core\Helper\Curl;

class MassSync extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderDataSend
     */
    protected $orderDataSend;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

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
     * MassSync constructor.
     * @param Context $context
     * @param Filter $filter
     * @param OrderDataSend $orderDataSend
     * @param CollectionFactory $collectionFactory
     * @param OmotechLogger $logger
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderDataSend $orderDataSend,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        Curl $curl,
        OmotechLogger $logger,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context, $filter);
        $this->orderDataSend = $orderDataSend;
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $countUpdateOrder = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }

            try {
                $this->orderDataSend->orderDataSend($order);
                $countUpdateOrder++;
            } catch (\Exception $e) {}
        }

        $countNonUpdateOrder = $collection->count() - $countUpdateOrder;
        if ($countUpdateOrder || $countNonUpdateOrder) {
            $this->messageManager->addNoticeMessage(__(
                'Orders synced: %1 Orders failed: %2',
                $countUpdateOrder,
                $countNonUpdateOrder
            ));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
