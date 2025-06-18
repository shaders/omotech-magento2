<?php
namespace Omotech\Order\Model\OrderData;

use Omotech\Order\Block\Adminhtml\System\Config;
use Omotech\Order\Block\Adminhtml\System\Config\OrderSyncStatus;
use Omotech\Core\Helper\Curl;
use Omotech\Core\Logger\Logger as OmotechLogger;
use Omotech\Core\Helper\Data as OmotechHelper;
use Omotech\Core\Helper\Data as CoreHelper;
use Omotech\Order\Helper\Data as OmotechOrderHelper;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Omotech\Customer\Model\Customer;

class OrderDataSend
{
    /**
     * @var OmotechOrderHelper
     */
    private $omotechOrderHelper;

    /**
     * @var OmotechHelper
     */
    private $omotechHelper;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var OmotechLogger
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $_productRepositoryFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var CustomerResource
     */
    protected $customerResource;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CustomerModel
     */
    protected  $customerModel;

    /**
     * @var CustomerModel
     */
    protected  $coreHelper;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * OrderDataSend constructor.
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param ImageFactory $imageHelperFactory
     * @param OmotechOrderHelper $omotechOrderHelper
     * @param CoreHelper $omotechHelper
     * @param ConfigInterface $configInterface
     * @param Curl $curl
     * @param OmotechLogger $logger
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param StoreRepositoryInterface $storeRepository
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerModel $customerModel
     * @param AddressRepositoryInterface $addressRepository
     * @param Attribute $eavAttribute
     * @param CoreHelper $coreHelper
     * @param CustomerResource $customerResource
     * @param CartRepositoryInterface $quoteRepository
     * @param Customer $customer
     * @param TimezoneInterface $dateTime
     */
    public function __construct(
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        ImageFactory $imageHelperFactory,
        OmotechOrderHelper $omotechOrderHelper,
        OmotechHelper $omotechHelper,
        ConfigInterface $configInterface,
        Curl $curl,
        OmotechLogger $logger,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreRepositoryInterface $storeRepository,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        CustomerModel $customerModel,
        AddressRepositoryInterface $addressRepository,
        Attribute $eavAttribute,
        CoreHelper $coreHelper,
        CustomerResource $customerResource,
        CartRepositoryInterface $quoteRepository,
        Customer $customer,
        TimezoneInterface $dateTime
    ) {
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->omotechOrderHelper = $omotechOrderHelper;
        $this->omotechHelper = $omotechHelper;
        $this->configInterface = $configInterface;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeRepository = $storeRepository;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerModel = $customerModel;
        $this->addressRepository = $addressRepository;
        $this->eavAttribute = $eavAttribute;
        $this->coreHelper = $coreHelper;
        $this->customerResource = $customerResource;
        $this->quoteRepository = $quoteRepository;
        $this->customer =  $customer;
        $this->dateTime = $dateTime;
    }

    /**
     * @param $order
     * @return array
     * @throws GuzzleException
     * @throws \Exception
     */
    public function orderDataSend($order): array
    {
        $return = [];

        try {
            $customerId = $order->getCustomerId();
            $quoteModel = null;
            try{
                $quoteModel = $this->quoteRepository->get($order->getQuoteId());
                $quote = $quoteModel;
            }catch (\Exception $e){
                $quote = $order;
            }

            if ($customerId) {
                $pwCustomer = $this->customer->updateCustomer($this->getCustomer($customerId));
            }else{
                $customerEmail = $quote->getBillingAddress()->getEmail();
                $contact['email'] = $customerEmail;
                $contact['firstName'] = $quote->getBillingAddress()->getFirstname();
                $contact['lastName'] = $quote->getBillingAddress()->getLastname();
                $contact['phone'] = $quote->getBillingAddress()->getTelephone();
                $contact['fieldValues'] = [];
                $pwCustomer = $this->customer->createCustomer($contact,$order->getStoreId());
            }

            $timezone = $this->dateTime->getConfigTimezone(\Magento\Store\Model\ScopeInterface::SCOPE_STORES, $order->getStoreId());

            foreach ($order->getAllVisibleItems() as $item) {
                $imageUrl = $this->imageHelperFactory->create()
                            ->init($item->getProduct(), 'product_thumbnail_image')->getUrl();
                $categories = $item->getProduct()->getCategoryCollection()->addAttributeToSelect('name');
                $categoriesName = [];
                foreach($categories as $category) {
                    $categoriesName[] = $category->getName();
                }
                $categoriesName = implode(', ', $categoriesName);

                $items[] = [
                    "productId" => $item->getProductId(),
                    "name" => $item->getName(),
                    "price" => (float)$item->getPrice(),
                    "quantity" => $item->getQtyOrdered(),
                    "sku" => $item->getSku(),
                    "description" => $item->getDescription(),
                    "imageUrl" => $imageUrl,
                    "productUrl" => $item->getProduct()->getProductUrl(),
                    "category" => $categoriesName
                ];
            }

            $data = [
                "orderId" => $order->getId(),
                "source" => 1,
                "email" => $order->getCustomerEmail(),
                "items" => $items,
                "createdDate" => $this->dateTime->date(strtotime($order->getCreatedAt()),NULL,$timezone)->format('Y-m-d\TH:i:sP'),
                "updatedDate" => $this->dateTime->date(strtotime($order->getUpdatedAt()),NULL,$timezone)->format('Y-m-d\TH:i:sP'),
                "shippingMethod" => $order->getShippingMethod(),
                "totalPrice" => (float)$order->getGrandTotal(),
                "shippingAmount" => (float)$order->getShippingAmount(),
                "taxAmount" => (float)$order->getTaxAmount(),
                "discountAmount" => (float)$order->getDiscountAmount(),
                "currency" => $order->getOrderCurrencyCode(),
                "orderNumber" => $order->getIncrementId(),
                "status" => $order->getStatus(),
                "customerId" => $pwCustomer
            ];

            $email = $order->getCustomerEmail();
            $syncStatus = $order->getPwOrderSyncStatus();

            if ($order->getStatus() == 'canceled') {
                $return = $this->curl->postEvent("PW_OrderCanceled", $email, $email, $data);
            } else {
                if ($syncStatus != OrderSyncStatus::SYNCED) {
                    if ($quoteModel) {
                        $syncStatus = $quote->getPwOrderSyncStatus();
                    }
                }

                if($syncStatus == OrderSyncStatus::SYNCED) {
                    $return = $this->curl->postEvent("PW_OrderUpdated", $email, $email, $data);
                }else{
                    $return = $this->curl->postEvent("PW_OrderCreated", $email, $email, $data);
                }
            }

            $order->setData(OrderSyncStatus::PW_SYNC_STATUS, OrderSyncStatus::SYNCED)->save();
        } catch (\Exception $e) {
            $order->setData(OrderSyncStatus::PW_SYNC_STATUS, OrderSyncStatus::FAIL_SYNCED)->save();
            throw $e;
        }

        return $return;
    }

    /**
     * @param $customerId
     * @return object
     */
    private function getCustomer($customerId): object
    {
        $customerModel = $this->customerFactory->create();
        $this->customerResource->load($customerModel, $customerId);
        return $customerModel;
    }
}
