<?php
namespace TrustPayments\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class TrustPaymentsRouteServiceProvider extends RouteServiceProvider
{

    /**
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->post('trustpayments/update-transaction', 'TrustPayments\Controllers\PaymentNotificationController@updateTransaction');
        $router->get('trustpayments/fail-transaction/{id}', 'TrustPayments\Controllers\PaymentProcessController@failTransaction')->where('id', '\d+');
        $router->post('trustpayments/pay-order', 'TrustPayments\Controllers\PaymentProcessController@payOrder');
        $router->get('trustpayments/download-invoice/{id}', 'TrustPayments\Controllers\PaymentTransactionController@downloadInvoice')->where('id', '\d+');
        $router->get('trustpayments/download-packing-slip/{id}', 'TrustPayments\Controllers\PaymentTransactionController@downloadPackingSlip')->where('id', '\d+');
    }
}