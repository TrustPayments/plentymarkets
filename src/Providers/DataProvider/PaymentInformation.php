<?php
namespace TrustPayments\Providers\DataProvider;

use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use TrustPayments\Services\TrustPaymentsSdkService;
use Plenty\Plugin\ConfigRepository;

class PaymentInformation
{

    public function call(Twig $twig, $arg): string
    {
        $order = $arg[0];
        $payments = pluginApp(PaymentRepositoryContract::class)->getPaymentsByOrderId($order['id']);
        foreach (array_reverse($payments) as $payment) {
            if ($payment->status != Payment::STATUS_CANCELED) {
                $transactionId = null;
                foreach ($payment->properties as $property) {
                    if ($property->typeId == PaymentProperty::TYPE_TRANSACTION_ID) {
                        $transactionId = $property->value;
                    }
                }
                if (! empty($transactionId)) {
                    $transaction = pluginApp(TrustPaymentsSdkService::class)->call('getTransaction', [
                        'id' => $transactionId
                    ]);
                    if (is_array($transaction) && isset($transaction['error'])) {
                        return "";
                    } else {
                        return $twig->render('trustPayments::PaymentInformation', [
                            'order' => $order,
                            'transaction' => $transaction,
                            'payment' => $payment,
                            'downloadInvoice' => pluginApp(ConfigRepository::class)->get('trustPayments.confirmation_invoice') === "true",
                            'downloadPackingSlip' => pluginApp(ConfigRepository::class)->get('trustPayments.confirmation_packing_slip') === "true"
                        ]);
                    }
                } else {
                    return "";
                }
            }
        }

        return "";
    }
}