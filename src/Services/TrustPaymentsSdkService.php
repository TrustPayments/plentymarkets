<?php
namespace TrustPayments\Services;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

class TrustPaymentsSdkService
{

    const GATEWAY_BASE_PATH = 'https://ep.trustpayments.com/';

    /**
     *
     * @var LibraryCallContract
     */
    private $libCall;

    /**
     *
     * @var ConfigRepository
     */
    private $config;

    /**
     *
     * @param LibraryCallContract $libCall
     * @param ConfigRepository $config
     */
    public function __construct(LibraryCallContract $libCall, ConfigRepository $config)
    {
        $this->libCall = $libCall;
        $this->config = $config;
    }

    /**
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function call(string $method, array $parameters)
    {
        $parameters['gatewayBasePath'] = self::GATEWAY_BASE_PATH;
        $parameters['apiUserId'] = $this->config->get('trustPayments.api_user_id');
        $parameters['apiUserKey'] = $this->config->get('trustPayments.api_user_key');
        if (!isset($parameters['spaceId']) || $parameters['spaceId'] == 0) {
            $parameters['spaceId'] = $this->config->get('trustPayments.space_id');
        }
        return $this->libCall->call('trustPayments::' . $method, $parameters);
    }
}
