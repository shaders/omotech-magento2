<?php

namespace Omotech\AbandonedCart\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AbandonedCartDataProvider extends SearchResult
{
    protected function _initSelect()
    {
        parent::_initSelect();
		$this->getSelect()
			->join(
				['quote_address'],
				'main_table.entity_id = quote_address.quote_id',
                ['quote_address.quote_id','quote_address.email'],
			)
			->where("main_table.is_active = 1 AND quote_address.address_type = 'billing' and main_table.items_count>0 and (main_table.customer_email is not null or quote_address.email is not null)");
        return $this;
    }
}
