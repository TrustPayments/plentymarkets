<?php
namespace TrustPayments\Migrations;

use TrustPayments\Models\Webhook;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class UpdateWebhookSpaceId
{

    /**
     *
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->updateTable(Webhook::class);
    }
}