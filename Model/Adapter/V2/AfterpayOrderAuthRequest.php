<?php
/**
 * Magento 2 extensions for Afterpay Payment
 *
 * @author Afterpay
 * @copyright 2016-2019 Afterpay https://www.afterpay.com
 */
namespace Afterpay\Afterpay\Model\Adapter\V2;

use \Afterpay\Afterpay\Model\Adapter\Afterpay\Call;
use \Afterpay\Afterpay\Model\Config\Payovertime as PayovertimeConfig;
use \Magento\Framework\ObjectManagerInterface as ObjectManagerInterface;
use \Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Afterpay\Afterpay\Helper\Data as Helper;

/**
 * Class AfterpayOrderAuthRequest
 * @package Afterpay\Afterpay\Model\Adapter\V2
 */
class AfterpayOrderAuthRequest
{
    protected $afterpayApiCall;
    protected $afterpayConfig;
    protected $objectManagerInterface;
    protected $storeManagerInterface;
    protected $jsonHelper;
    protected $helper;

    /**
     * AfterpayOrderAuthRequest constructor.
     * @param Call $afterpayApiCall
     * @param PayovertimeConfig $afterpayConfig
     * @param ObjectManagerInterface $objectManagerInterface
     * @param toreManagerInterface $storeManagerInterface
     * @param JsonHelper $jsonHelper
     * @param Helper $afterpayHelper
     */
    public function __construct(
        Call $afterpayApiCall,
        PayovertimeConfig $afterpayConfig,
        ObjectManagerInterface $objectManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        JsonHelper $jsonHelper,
        Helper $afterpayHelper
    ) {
        $this->afterpayApiCall = $afterpayApiCall;
        $this->afterpayConfig = $afterpayConfig;
        $this->objectManagerInterface = $objectManagerInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $afterpayHelper;
    }

    /**
     * @param $token
     * @param $merchant_order_id
     * @return mixed|\Zend_Http_Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate($token, $merchant_order_id)
    {
        $requestData = $this->_buildAuthRequest($token, $merchant_order_id);

        try {
            $response = $this->afterpayApiCall->send(
                $this->afterpayConfig->getApiUrl('v2/payments/auth'),
                $requestData,
                \Magento\Framework\HTTP\ZendClient::POST
            );
        } catch (\Exception $e) {
            $response = $this->objectManagerInterface->create('Afterpay\Afterpay\Model\Payovertime');
            $response->setBody($this->jsonHelper->jsonEncode([
                'error' => 1,
                'message' => $e->getMessage()
            ]));
        }

        return $response;
    }
	/**
     * @param $token
     * @param $merchant_order_id
     * @return array
     */
    protected function _buildAuthRequest($token, $merchant_order_id)
    {
        $params['requestId'] = uniqid();
        $params['merchantReference'] = $merchant_order_id;
        $params['token'] = $token;

        return $params;
    }
}