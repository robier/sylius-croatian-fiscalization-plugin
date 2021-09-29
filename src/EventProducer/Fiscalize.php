<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\EventProducer;

use Robier\Fiscalization\Client\Production;
use Robier\SyliusCroatianFiscalizationPlugin\Converter\BillConverter;
use Sylius\Component\Core\Model\PaymentInterface;

final class Fiscalize
{
    public function __construct(
        private BillConverter $billConverter,
        private Production $client
    )
    {
        // noop
    }

    public function __invoke(PaymentInterface $payment)
    {
        $order = $payment->getOrder();

        $bill = ($this->billConverter)($order);

        try{
            $response = $this->client->send($bill);
        } catch (\Exception $e) {
            // @todo log failure
            dd($e);
        }

        // @todo log success

        dd($response);
    }
}