<?php
namespace TrustPayments\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use TrustPayments\Helper\PaymentHelper;
use TrustPayments\Services\PaymentService;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use TrustPayments\Methods\CreditDebitCardPaymentMethod;
use TrustPayments\Methods\InvoicePaymentMethod;
use TrustPayments\Methods\OnlineBankingPaymentMethod;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use TrustPayments\Methods\AlipayPaymentMethod;
use TrustPayments\Methods\BankTransferPaymentMethod;
use TrustPayments\Methods\CashuPaymentMethod;
use TrustPayments\Methods\DaoPayPaymentMethod;
use TrustPayments\Methods\DirectDebitSepaPaymentMethod;
use TrustPayments\Methods\DirectDebitUkPaymentMethod;
use TrustPayments\Methods\EpsPaymentMethod;
use TrustPayments\Methods\GiropayPaymentMethod;
use TrustPayments\Methods\IDealPaymentMethod;
use TrustPayments\Methods\MasterPassPaymentMethod;
use TrustPayments\Methods\PayboxPaymentMethod;
use TrustPayments\Methods\PaydirektPaymentMethod;
use TrustPayments\Methods\PaylibPaymentMethod;
use TrustPayments\Methods\PayPalPaymentMethod;
use TrustPayments\Methods\PaysafecardPaymentMethod;
use TrustPayments\Methods\PoliPaymentMethod;
use TrustPayments\Methods\Przelewy24PaymentMethod;
use TrustPayments\Methods\QiwiPaymentMethod;
use TrustPayments\Methods\SkrillPaymentMethod;
use TrustPayments\Methods\SofortBankingPaymentMethod;
use TrustPayments\Methods\TenpayPaymentMethod;
use TrustPayments\Methods\TrustlyPaymentMethod;
use TrustPayments\Methods\TwintPaymentMethod;
use TrustPayments\Procedures\RefundEventProcedure;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\Cron\Services\CronContainer;
use TrustPayments\Services\WebhookCronHandler;
use TrustPayments\Contracts\WebhookRepositoryContract;
use TrustPayments\Repositories\WebhookRepository;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;

class TrustPaymentsServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->getApplication()->register(TrustPaymentsRouteServiceProvider::class);
        $this->getApplication()->bind(WebhookRepositoryContract::class, WebhookRepository::class);
        $this->getApplication()->bind(RefundEventProcedure::class);
    }

    /**
     * Boot services of the Trust Payments plugin.
     *
     * @param PaymentMethodContainer $payContainer
     */
    public function boot(Dispatcher $eventDispatcher, PaymentHelper $paymentHelper, PaymentService $paymentService, BasketRepositoryContract $basketRepository, OrderRepositoryContract $orderRepository, PaymentMethodContainer $payContainer, PaymentMethodRepositoryContract $paymentMethodService, EventProceduresService $eventProceduresService, CronContainer $cronContainer)
    {
        $this->registerPaymentMethod($payContainer, 1457546097615, AlipayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097602, BankTransferPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477573906453, CashuPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097597, CreditDebitCardPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477574926155, DaoPayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097601, DirectDebitSepaPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1464254757862, DirectDebitUkPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097609, EpsPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097610, GiropayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1461674005576, IDealPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097598, InvoicePaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097621, MasterPassPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1460954915005, OnlineBankingPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1484231986107, PayboxPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097640, PaydirektPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1476259715349, PaylibPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097613, PayPalPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097612, PaysafecardPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097618, PoliPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097617, Przelewy24PaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097616, QiwiPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097614, SkrillPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097603, SofortBankingPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1477574502344, TenpayPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097619, TrustlyPaymentMethod::class);
        $this->registerPaymentMethod($payContainer, 1457546097639, TwintPaymentMethod::class);

        // Register Refund Event Procedure
        $eventProceduresService->registerProcedure('plentyTrustPayments', ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Rückzahlung der Trust Payments-Zahlung',
            'en' => 'Refund the Trust Payments payment'
        ], 'TrustPayments\Procedures\RefundEventProcedure@run');

        $eventDispatcher->listen(GetPaymentMethodContent::class, function (GetPaymentMethodContent $event) use ($paymentHelper, $basketRepository, $paymentService, $paymentMethodService) {
            if ($paymentHelper->isTrustPaymentsPaymentMopId($event->getMop())) {
                $result = $paymentService->getPaymentContent($basketRepository->load(), pluginApp(BasketService::class)->getBasketForTemplate(), $paymentMethodService->findByPaymentMethodId($event->getMop()));
                $event->setValue(isset($result['content']) ? $result['content'] : null);
                $event->setType(isset($result['type']) ? $result['type'] : '');
            }
        });

        $eventDispatcher->listen(ExecutePayment::class, function (ExecutePayment $event) use ($paymentHelper, $orderRepository, $paymentService, $paymentMethodService) {
            if ($paymentHelper->isTrustPaymentsPaymentMopId($event->getMop())) {
                $result = $paymentService->executePayment($orderRepository->findOrderById($event->getOrderId()), $paymentMethodService->findByPaymentMethodId($event->getMop()));
                $event->setValue(isset($result['content']) ? $result['content'] : null);
                $event->setType(isset($result['type']) ? $result['type'] : '');
            }
        });

        $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, WebhookCronHandler::class);
    }

    private function registerPaymentMethod($payContainer, $id, $class)
    {
        $payContainer->register('trustPayments::' . $id, $class, [
            AfterBasketChanged::class,
            AfterBasketCreate::class
        ]);
    }
}