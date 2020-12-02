<?php
use TrustPayments\Sdk\Service\TransactionService;
use TrustPayments\Sdk\Model\EntityQuery;
use TrustPayments\Sdk\Model\EntityQueryFilter;
use TrustPayments\Sdk\Model\EntityQueryOrderBy;
use TrustPayments\Sdk\Model\EntityQueryOrderByType;
use TrustPayments\Sdk\Model\EntityQueryFilterType;
use TrustPayments\Sdk\Model\CriteriaOperator;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));

$spaceId = SdkRestApi::getParam('spaceId');

$service = new TransactionService($client);
$query = new EntityQuery();
$filter = new EntityQueryFilter();
$filter->setType(EntityQueryFilterType::LEAF);
$filter->setOperator(CriteriaOperator::EQUALS);
$filter->setFieldName('merchantReference');
$filter->setValue(SdkRestApi::getParam('merchantReference'));
$query->setFilter($filter);
$orderBy = new EntityQueryOrderBy();
$orderBy->setFieldName('createdOn');
$orderBy->setSorting(EntityQueryOrderByType::DESC);
$query->setOrderBys($orderBy);
$query->setNumberOfEntities(1);
$transactions = $service->search($spaceId, $query);

return TrustPaymentsSdkHelper::convertData(current($transactions));