<?php
use TrustPayments\Sdk\Model\WebhookUrlCreate;
use TrustPayments\Sdk\Model\WebhookListenerCreate;
use TrustPayments\Sdk\Service\WebhookUrlService;
use TrustPayments\Sdk\Service\WebhookListenerService;

require_once __DIR__ . '/TrustPaymentsSdkHelper.php';

class WebhookEntity
{

    private $id;

    private $name;

    private $states;

    public function __construct($id, $name, array $states)
    {
        $this->id = $id;
        $this->name = $name;
        $this->states = $states;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStates()
    {
        return $this->states;
    }
}

$webhookEntities = [];
$webhookEntities[] = new WebhookEntity(1472041829003, 'Transaction', [
    \TrustPayments\Sdk\Model\TransactionState::AUTHORIZED,
    \TrustPayments\Sdk\Model\TransactionState::DECLINE,
    \TrustPayments\Sdk\Model\TransactionState::FAILED,
    \TrustPayments\Sdk\Model\TransactionState::FULFILL,
    \TrustPayments\Sdk\Model\TransactionState::VOIDED,
    \TrustPayments\Sdk\Model\TransactionState::COMPLETED
], 'update-transaction');
$webhookEntities[] = new WebhookEntity(1472041816898, 'Transaction Invoice', [
    \TrustPayments\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE,
    \TrustPayments\Sdk\Model\TransactionInvoiceState::PAID,
    \TrustPayments\Sdk\Model\TransactionInvoiceState::DERECOGNIZED
], 'update-transaction-invoice');
$webhookEntities[] = new WebhookEntity(1472041839405, 'Refund', [
    \TrustPayments\Sdk\Model\RefundState::SUCCESSFUL,
    \TrustPayments\Sdk\Model\RefundState::FAILED
]);

$client = TrustPaymentsSdkHelper::getApiClient(SdkRestApi::getParam('gatewayBasePath'), SdkRestApi::getParam('apiUserId'), SdkRestApi::getParam('apiUserKey'));
$spaceId = SdkRestApi::getParam('spaceId');

$webhookUrlService = new WebhookUrlService($client);
$webhookListenerService = new WebhookListenerService($client);

$query = new \TrustPayments\Sdk\Model\EntityQuery();
$query->setNumberOfEntities(1);
$filter = new \TrustPayments\Sdk\Model\EntityQueryFilter();
$filter->setType(\TrustPayments\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    TrustPaymentsSdkHelper::createEntityFilter('url', SdkRestApi::getParam('notificationUrl')),
    TrustPaymentsSdkHelper::createEntityFilter('state', \TrustPayments\Sdk\Model\CreationEntityState::ACTIVE)
]);
$query->setFilter($filter);
$webhookResult = $webhookUrlService->search($spaceId, $query);
if (empty($webhookResult)) {
    $webhookUrlRequest = new WebhookUrlCreate();
    $webhookUrlRequest->setState(\TrustPayments\Sdk\Model\CreationEntityState::ACTIVE);
    $webhookUrlRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId'));
    $webhookUrlRequest->setUrl(SdkRestApi::getParam('notificationUrl'));
    $webhookUrl = $webhookUrlService->create($spaceId, $webhookUrlRequest);
} else {
    $webhookUrl = $webhookResult[0];
}

$query = new \TrustPayments\Sdk\Model\EntityQuery();
$filter = new \TrustPayments\Sdk\Model\EntityQueryFilter();
$filter->setType(\TrustPayments\Sdk\Model\EntityQueryFilterType::_AND);
$filter->setChildren([
    TrustPaymentsSdkHelper::createEntityFilter('state', \TrustPayments\Sdk\Model\CreationEntityState::ACTIVE),
    TrustPaymentsSdkHelper::createEntityFilter('url.id', $webhookUrl->getId())
]);
$query->setFilter($filter);
$existingListeners = $webhookListenerService->search($spaceId, $query);

foreach ($webhookEntities as $webhookEntity) {
    $exists = false;
    foreach ($existingListeners as $existingListener) {
        if ($existingListener->getEntity() == $webhookEntity->getId()) {
            $exists = true;
        }
    }

    if (! $exists) {
        $webhookListenerRequest = new WebhookListenerCreate();
        $webhookListenerRequest->setState(\TrustPayments\Sdk\Model\CreationEntityState::ACTIVE);
        $webhookListenerRequest->setEntity($webhookEntity->getId());
        $webhookListenerRequest->setEntityStates($webhookEntity->getStates());
        $webhookListenerRequest->setName('plentymarkets ' . SdkRestApi::getParam('storeId') . ' ' . $webhookEntity->getName());
        $webhookListenerRequest->setUrl($webhookUrl);

        $webhookListenerService->create($spaceId, $webhookListenerRequest);
    }
}