<?php
use TrustPayments\Sdk\Service\TransactionService;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');
$transactionId = SdkRestApi::getParam('transactionId');

$service = new TransactionService($client);
$possiblePaymentMethods = $service->fetchPossiblePaymentMethods($spaceId, $transactionId);
if ($possiblePaymentMethods != null && ! empty($possiblePaymentMethods)) {
    return true;
} else {
    return false;
}