<?php

namespace Pushwoosh\Order\Controller\Adminhtml\Order;

use Pushwoosh\Order\Block\Adminhtml\System\Config\OrderSyncStatus;
use Pushwoosh\Order\Model\OrderData\OrderDataSend;
use Pushwoosh\Core\Logger\Logger as PushwooshLogger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Quote\Api\CartRepositoryInterface;
use Pushwoosh\Core\Helper\Curl;

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
     * @var PushwooshLogger
     */
    private $logger;

    /**
     * MassSync constructor.
     * @param Context $context
     * @param Filter $filter
     * @param OrderDataSend $orderDataSend
     * @param CollectionFactory $collectionFactory
     * @param PushwooshLogger $logger
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderDataSend $orderDataSend,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        Curl $curl,
        PushwooshLogger $logger,
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
