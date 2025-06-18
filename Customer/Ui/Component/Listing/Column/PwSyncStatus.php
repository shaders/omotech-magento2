<?php

namespace Omotech\Customer\Ui\Component\Listing\Column;

use Omotech\Customer\Model\Config\CronConfig;

use Magento\Ui\Component\Listing\Columns\Column;
use Omotech\Customer\Model\Customer;

class PwSyncStatus extends Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->getFieldLabel($item);
            }
        }
        return $dataSource;
    }

    /**
     * Retrieve field label
     *
     * @param array $item
     * @return string
     */
    private function getFieldLabel(array $item)
    {
        $pwSyncStatus = (int)$item[Customer::PW_SYNC_STATUS];
        if ($pwSyncStatus === CronConfig::SYNCED) {
            return __('Synced');
        } elseif ($pwSyncStatus === CronConfig::NOT_SYNCED) {
            return __('Not Synced');
        } elseif ($pwSyncStatus === CronConfig::FAIL_SYNCED) {
            return __('Not Synced');
        }
        return __('Something Wrong');
    }
}
