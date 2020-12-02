<?php
use TrustPayments\Sdk\Service\TransactionInvoiceService;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionInvoiceService($client);
$transactionInvoice = $service->read($spaceId, SdkRestApi::getParam('id'));

return TrustPaymentsSdkHelper::convertData($transactionInvoice);