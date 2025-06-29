<?php

namespace Omotech\Web\Controller\ServiceWorker;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\DirectoryList;


class Index extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        Context $context,
        DirectoryList $directoryList
    )
    {
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    public function execute()
    {
        $filePath = dirname(__DIR__, 2) . '/view/frontend/web/service-worker.js';

        if (!file_exists($filePath)) {
            return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(404)->setContents('Not Found');
        }

        $content = file_get_contents($filePath);
        return $this->resultFactory->create(ResultFactory::TYPE_RAW)
            ->setHeader('Content-Type', 'text/javascript', true)
            ->setHeader('Service-Worker-Allowed', '/', true)
            ->setContents($content);
    }
}
