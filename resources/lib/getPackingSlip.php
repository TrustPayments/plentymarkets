<?php
use TrustPayments\Sdk\Service\TransactionService;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionService($client);
$invoiceDocument = $service->getPackingSlip($spaceId, SdkRestApi::getParam('id'));

return TrustPaymentsSdkHelper::convertData($invoiceDocument);