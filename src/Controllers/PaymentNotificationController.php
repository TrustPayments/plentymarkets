<?php
namespace TrustPayments\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use TrustPayments\Helper\PaymentHelper;
use TrustPayments\Services\PaymentService;
use Plenty\Plugin\Log\Loggable;
use TrustPayments\Services\TrustPaymentsSdkService;
use TrustPayments\Contracts\WebhookRepositoryContract;

class PaymentNotificationController extends Controller
{

    use Loggable;

    /**
     *
     * @var Request
     */
    private $request;

    /**
     *
     * @var Response
     */
    private $response;

    /**
     *
     * @var ConfigRepository
     */
    private $config;

    /**
     *
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     *
     * @var PaymentService
     */
    private $paymentService;

    /**
     *
     * @var TrustPaymentsSdkService
     */
    private $sdkService;

    /**
     *
     * @var WebhookRepositoryContract
     */
    private $webhookRepository;

    /**
     * PaymentController constructor.
     *
     * @param Request $request
     * @param Response $response
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param TrustPaymentsSdkService $sdkService
     * @param WebhookRepositoryContract $webhookRepository
     */
    public function __construct(Request $request, Response $response, ConfigRepository $config, PaymentHelper $paymentHelper, PaymentService $paymentService, TrustPaymentsSdkService $sdkService, WebhookRepositoryContract $webhookRepository)
    {
        $this->request = $request;
        $this->response = $response;
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->paymentService = $paymentService;
        $this->sdkService = $sdkService;
        $this->webhookRepository = $webhookRepository;
    }

    public function updateTransaction()
    {
        $webhookRequest = json_decode($this->request->getContent());
        $this->getLogger(__METHOD__)->info('webhookRequest', $webhookRequest);

        if (in_array(strtolower($webhookRequest->listenerEntityTechnicalName), [
            'transaction',
            'transactioninvoice',
            'refund'
        ])) {
            $this->processWebhook($webhookRequest);
        }
        return "OK";
    }

    protected function processWebhook($webhook)
    {
        $transactionId = $webhook->entityId;
        try {
            // this makes a call to whatever the listenerEntityTechnicalName is e.g. getTransaction
            $transaction = $this->sdkService->call('get' . ucfirst($webhook->listenerEntityTechnicalName), [
                'id' => $transactionId,
                'spaceId' => $webhook->spaceId
            ]);

        } catch (Exception $e) {
            $this->getLogger(__METHOD__)->error('Error calling SDK service' . $e->getMessage());
        }

        if (empty($transaction)) {
            $this->getLogger(__METHOD__)->error('The ' . $webhook->listenerEntityTechnicalName . ' was not found.', $transactionId);
            return true;
        }

        if (is_array($transaction) && isset($transaction['error'])) {
            throw new \Exception($transaction['error_msg']);
        }

        return $this->paymentHelper->updatePlentyPayment($transaction);
    }
}