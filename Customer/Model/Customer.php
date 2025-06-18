<?php

namespace Omotech\Customer\Model;

use Omotech\Core\Helper\Curl;
use Omotech\Core\Helper\Data as CoreHelper;
use Omotech\Core\Logger\Logger as OmotechLogger;
use Omotech\Customer\Helper\Data as CustomerHelper;
use Omotech\Customer\Model\Config\CronConfig;
use \Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerResourceCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer as MageCustomer;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
class Customer
{
    const PW_CUSTOMER_ID = 'pw_customer_id';

    const PW_SYNC_STATUS = "pw_sync_status";

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var CustomerResourceCollectionFactory
     */
    protected $customerResourceCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerResource
     */
    protected $customerResource;



    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OmotechLogger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * Customer constructor.
     * @param CustomerResourceCollectionFactory $customerResourceCollectionFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param CustomerResource $customerResource
     * @param CustomerHelper $customerHelper
     * @param CoreHelper $coreHelper
     * @param Curl $curl
     * @param Attribute $eavAttribute
     * @param TypeListInterface $cacheTypeList
     * @param OmotechLogger $logger
     * @param SubscriberFactory $subscriberFactory
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CustomerResourceCollectionFactory $customerResourceCollectionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        CustomerResource $customerResource,
        CustomerHelper $customerHelper,
        CoreHelper $coreHelper,
        Curl $curl,
        Attribute $eavAttribute,
        TypeListInterface $cacheTypeList,
        OmotechLogger $logger,
        SubscriberFactory $subscriberFactory,
        AddressRepositoryInterface $addressRepository,
        StoreManagerInterface $storeManager
    ) {

        $this->customerResourceCollectionFactory = $customerResourceCollectionFactory;
        $this->customerRepository = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->customerHelper = $customerHelper;
        $this->coreHelper = $coreHelper;
        $this->curl = $curl;
        $this->cacheTypeList = $cacheTypeList;
        $this->eavAttribute = $eavAttribute;
        $this->logger = $logger;
        $this->subscriberFactory = $subscriberFactory;
        $this->addressRepository = $addressRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $customerId
     * @return MageCustomer
     */
    public function getCustomerById($customerId){
        $customerModel = $this->customerFactory->create();
        $this->customerResource->load($customerModel, $customerId);

        return $customerModel;
    }


    /**
     * @param null $billingId
     * @return string|null
     */
    private function getTelephone($billingId = null)
    {
        if ($billingId) {
           try{
              $address = $this->addressRepository->getById($billingId);
              return $address->getTelephone();
          }catch (\Exception $exception){

           }
        }
        return null;
    }

    public function getFieldValues($customer)
    {
        $fieldValues = [];
        $customAttributes = $this->customerHelper->getMapCustomFields();
        if (!empty($customAttributes)) {
            foreach (json_decode($customAttributes) as $attribute) {
                $value ='';
                if(strncmp($attribute->customer_field_id,'shipping__',10) === 0 ){
                    if($customer->getDefaultShippingAddress()){
                        $value = $customer->getDefaultShippingAddress()->getData(substr($attribute->customer_field_id,10));
                    }
                }elseif (strncmp($attribute->customer_field_id,'billing__',9) === 0){
                    if($customer->getDefaultBillingAddress()){
                        $value = $customer->getDefaultBillingAddress()->getData(substr($attribute->customer_field_id,9));
                    }
                }else{
                    if($attr = $customer->getResource()->getAttribute($attribute->customer_field_id)){
                        $value = $attr->getFrontend()->getValue($customer);
                    }else {
                        $value = $customer->getData($attribute->customer_field_id);
                    }
                }

                $fieldValues[$attribute->pw_customer_field_id] = $value;
            }
        }
        return $fieldValues;
    }

    /**
     * @param $customerId
     * @param $syncStatus
     * @param $contactId
     * @param $ecomCustomerId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function saveResult($customerId, $syncStatus, $ecomCustomerId)
    {
        $customerModel = $this->customerFactory->create();
        if ($customerId) {
            $this->customerResource->load($customerModel, $customerId);
        }

        $customerModel->setPwSyncStatus($syncStatus);
        if ($ecomCustomerId) {
            $customerModel->setPwCustomerId($ecomCustomerId);
        }
        $this->customerResource->save($customerModel);
    }


    public  function contactBody($customer): array
    {
        $contact['email'] = $customer->getEmail();
        $contact['firstName'] = $customer->getFirstname();
        $contact['lastName'] = $customer->getLastname();
        $contact['phone'] = $this->getTelephone($customer->getDefaultBilling());
        $contact['acceptsMarketing'] = (int)$this->subscriberFactory->create()->loadBySubscriberEmail($customer->getEmail(), $customer->getWebsiteId())->isSubscribed();

        $fieldValues = $this->getFieldValues($customer);
        foreach ($fieldValues as $key => $value) {
            $contact[$key] = $value;
        }

        return $contact;
    }


    public function updateCustomers()
    {
        $lastUpdate = $this->customerHelper->getLastCustomerUpdateSync();
        $numberOfCustomers = (int)$this->customerHelper->getNumberOfCustomers();
        $customers = $this->customerResourceCollectionFactory->create()
            ->addAttributeToSelect(self::PW_CUSTOMER_ID)
            ->addAttributeToFilter(self::PW_SYNC_STATUS, ['eq' => CronConfig::SYNCED])
            ->addAttributeToFilter('updated_at', ['gt' => $lastUpdate])
            ->setOrder('updated_at', 'asc')
            ->setPageSize($numberOfCustomers);
        foreach ($customers as $customer) {
            $this->updateCustomer($customer);
            $lastUpdate = $customer->getUpdatedAt();
        }
        if (isset($lastUpdate)) {
            $this->customerHelper->setLastCustomerUpdateSync($lastUpdate);
            $this->cSacheTypeList->cleanType('config');
        }
    }

    /**
     * @throws \Exception
     */
    public function createCustomer($data, $storeId): string {
        if(empty($data['email'])) {
            throw new \Exception("Email is required in createCustomer");
        }

        $data['acceptsMarketing'] = (int)$this->subscriberFactory->create()->loadBySubscriberEmail($data['email'],$this->storeManager->getStore($storeId)->getWebsiteId())->isSubscribed();
        return $this->updateCustomerInternal($data);
    }

    /**
     * @throws \Exception
     */
    public function updateCustomer($customer): string
    {
        $contactData = $this->contactBody($customer);
        $email = $this->updateCustomerInternal($contactData);
        $this->saveResult($customer->getId(), CronConfig::SYNCED, $email);

        return $email;
    }

    /**
     * @throws \Exception
     */
    private function updateCustomerInternal($contactData): string
    {
        try {
            $this->logger->info('updateCustomer internal', $contactData);

            $email = $contactData['email'];
            if (empty($email)) {
                throw new \Exception("email is missing from contact data");
            }

            unset($contactData['email']);
            $phone = $contactData['phone'];
            unset($contactData['phone']);

            $this->curl->registerDevice($email,  $email, 14, $contactData);
            if (!empty($phone)) {
                try {
                    $this->curl->registerDevice($phone,  $email,  18, $contactData);
                } catch (\Exception $e) {
                    $this->logger->error("MODULE: Customer  contact/sync" . $e->getMessage());
                    // do not rethrow exception on phone registration (could be due to wrong phone formatting)
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical("MODULE: Customer  contact/sync" . $e->getMessage());
            throw $e;
        }

        return $email;
    }

    public function syncCustomers(){
        $this->logger->info('syncCustomers');

        if (!$this->customerHelper->isCustomerSyncingEnabled()) {
            return;
        }

        $this->logger->info('syncCustomers enabled');

        // update already synced customers
        $this->updateCustomers();
        $numberOfCustomers = (int)$this->customerHelper->getNumberOfCustomers();

        // update not synced customers
        $customers = $this->customerResourceCollectionFactory->create()
            ->addAttributeToFilter([
                ['attribute' => self::PW_SYNC_STATUS,'null' => true ],
                ['attribute' => self::PW_SYNC_STATUS,'neq' => CronConfig::SYNCED ]
            ])
            ->setPageSize($numberOfCustomers);

        foreach ($customers as $customer) {
            try {
                $this->updateCustomer($customer);
            } catch (\Exception $e) {
                $this->logger->critical("MODULE: Customer " . $e->getMessage());
            }
        }
    }
}

