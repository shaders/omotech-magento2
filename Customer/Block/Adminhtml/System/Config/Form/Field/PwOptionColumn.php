<?php

declare(strict_types=1);
namespace Pushwoosh\Customer\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class PwOptionColumn extends Select
{
    private $curl;
    private $pwHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Pushwoosh\Core\Helper\Curl $curl,
        \Pushwoosh\Core\Helper\Data $pwHelper,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->curl = $curl;
        $this->pwHelper = $pwHelper;
    }
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);

    }
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        $fields=[];

        if($this->pwHelper->isEnabled()){
            $tags = $this->curl->getPushwooshTags();
            if (!isset($tags['tags']))
                return [];
            
            foreach ($tags['tags'] as $tag){
                $fields[]=['label' => $tag['name'], 'value'=> $tag['name']];
            }
        }

        return  $fields;

    }

    protected function getCustomerAtt()
    {
        $ret = [];
        $collection = $this->attrCollection->create();

        foreach ($collection as $item) {
            $ret[] = ['label' => $item->getFrontendLabel(), 'value' => $item->getId() ];
        }


        return $ret;
    }
}
