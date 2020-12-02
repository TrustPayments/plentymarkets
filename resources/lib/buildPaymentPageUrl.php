<?php
use TrustPayments\Sdk\Service\TransactionPaymentPageService;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

$spaceId = SdkRestApi::getParam('spaceId');
$id = SdkRestApi::getParam('id');

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$service = new TransactionPaymentPageService($client);
return $service->paymentPageUrl($spaceId, $id);