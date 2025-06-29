<?php

namespace Omotech\AbandonedCart\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Omotech\AbandonedCart\Block\System\Config\SyncAbandonedCartData;
use Omotech\AbandonedCart\Model\AbandonedCartSendData;
use Omotech\AbandonedCart\Model\Config\CronConfig;

class MassSync extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Omotech_AbandonedCart::abandonedcart_operation';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AbandonedCartSendData
     */
    protected $abandonedCartSendData;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param AbandonedCartSendData $abandonedCartSendData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        AbandonedCartSendData $abandonedCartSendData
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->abandonedCartSendData = $abandonedCartSendData;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $countSync = 0;
        $countFailSync = 0;

        foreach ($collection as $quote) {
            $quoteId = $quote->getEntityId();
            try {
                $this->abandonedCartSendData->sendAbandonedCartData($quoteId);
                $countSync++;
            } catch (\Exception $e) {
                $countFailSync++;
            }
        }

        if ($countSync || $countFailSync) {
            $this->messageManager->addNoticeMessage(__(
                'Carts synced: %1 Carts failed: %2',
                $countSync,
                $countFailSync
            ));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
