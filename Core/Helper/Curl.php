<?php
declare(strict_types=1);

namespace Omotech\Core\Helper;

use Omotech\Core\Helper\Data as OmotechHelper;
use Omotech\Core\Logger\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json  as JsonHelper;
use Magento\Framework\Phrase;

class Curl extends AbstractHelper
{
    public const API_VERSION = '/json/1.3/';
    public const HTTP_VERSION = '1.1';
    public const CONTENT_TYPE = 'application/json';

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Omotech\Core\Logger\Logger
     */
    private $logger;

    /**
     * @var \Omotech\Core\Helper\Data
     */
    private $omotechHelper;

    /**
     * Curl constructor.
     * @param Context $context
     * @param Client $client
     * @param JsonHelper $jsonHelper
     * @param Logger $logger
     * @param Data $omotechHelper
     */
    public function __construct(
        Context              $context,
        Client               $client = null,
        JsonHelper           $jsonHelper,
        Logger               $logger,
        OmotechHelper      $omotechHelper,
    ) {
        $this->client = $client ?: new Client();
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->omotechHelper = $omotechHelper;
        parent::__construct($context);
    }

    /**
     * Get Omotech Tags
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOmotechTags(): array {
        $apiKey = $this->omotechHelper->getApiKey();
        $appCode = $this->omotechHelper->getAppCode();

        try {
            $url = sprintf("https://rpc-api.svc-nue.pushwoosh.com/api/tags?application=%s&page=0&perPage=1000", $appCode);
            $headers = $this->getHeaders($apiKey);
            return $this->sendRequestRest('GET', $url, $headers, '');
        }
        catch (\Exception $e) {
            $this->logger->critical('MODULE Core: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create contacts
     *
     * @param string $hwid
     * @param string $userId
     * @param int $platform
     * @param array $data
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registerDevice(
        string $hwid,
        string $userId,
        int $platform,
        array $data = []
    ): array {
        $apiUrl = $this->omotechHelper->getApiUrl();
        $apiKey = $this->omotechHelper->getApiKey();
        $appCode = $this->omotechHelper->getAppCode();

        $url = $apiUrl . '/json/1.3/registerDevice';
        $headers = $this->getHeaders($apiKey);

        $request = [];
        $request['application'] = $appCode;
        $request['hwid'] = $hwid;
        $request['push_token'] = $hwid;
        $request['userId'] = $userId;
        $request['device_type'] = $platform;
        $request['language'] = 'en';
        $request['tags'] = $data;
        $bodyData = $this->jsonHelper->serialize($request);

        return $this->sendRequestRest('POST', $url, $headers, $bodyData);
    }

    /**
     * Post events
     *
     * @param string $eventName
     * @param string $hwid
     * @param string $userId
     * @param array $data
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postEvent(
        string $eventName,
        string $hwid,
        string $userId,
        array $data = []
    ): array {
        $apiUrl = $this->omotechHelper->getApiUrl();
        $apiKey = $this->omotechHelper->getApiKey();
        $appCode = $this->omotechHelper->getAppCode();

        $url = $apiUrl . '/json/1.3/postEvent';
        $headers = $this->getHeaders($apiKey);

        $request = [];
        $request['application'] = $appCode;
        $request['event'] = $eventName;
        $request['hwid'] = $hwid;
        $request['userId'] = $userId;
        $request['attributes'] = $data;
        $bodyData = $this->jsonHelper->serialize($request);

        return $this->sendRequestRest('POST', $url, $headers, $bodyData);
    }

    /**
     * Get headers
     *
     * @param string|null $apiKey
     *
     * @return array
     */
    private function getHeaders(?string $apiKey): array
    {
        return [
            'Content-Type'  => self::CONTENT_TYPE,
            'Authorization' => sprintf('Token %s', $apiKey)
        ];
    }

    /**
     * Send request REST
     *
     * @param string $urlEndpoint
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $bodyData
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequestRest(
        string $method,
        string $url,
        array $headers,
        string $bodyData = ''
    ): array {
        try {
            $request = [
                'METHOD'        => $method,
                'URL'           => $url,
                'HTTP VERSION'  => self::HTTP_VERSION,
                'HEADERS'       => $headers,
                'BODY DATA'     => $bodyData
            ];

            $this->logger->info('REQUEST', $request);

            $options = [];
            $options[\GuzzleHttp\RequestOptions::HEADERS] = $headers;

            if ($bodyData !== null) {
                $options[\GuzzleHttp\RequestOptions::BODY] = $bodyData;
            }

            $resultCurl = $this->client->request($method, $url, $options);
            $body = $resultCurl->getBody()->getContents();
            $response = $this->jsonHelper->unserialize($body);

            $this->logger->info('RESPONSE', $response);

            return $response;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $this->logger->critical('MODULE Core: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get message
     *
     * @param mixed $response
     *
     * @return \Magento\Framework\Phrase|string
     */
    private function getMessage(mixed $response)
    {
        if (is_array($response)) {
            if (isset($response['message'])) {
                return $response['message'];
            }
        }

        return __('An unknown error occurred. Please try again later');
    }
}
