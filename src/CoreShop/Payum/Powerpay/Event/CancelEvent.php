<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Payum\PowerpayBundle\Event;

use CoreShop\Component\Payment\Model\Payment;
use CoreShop\Component\Payment\Model\PaymentInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Cancel;
use Payum\Core\Payum;

class CancelEvent
{
    public function __construct(protected Payum $payum)
    {
    }

    /**
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function cancel(PaymentInterface $payment): void
    {
        if (!in_array($payment->getState(), [Payment::STATE_AUTHORIZED, Payment::STATE_PROCESSING], true)) {
            return;
        }

        $factoryName = $payment->getPaymentProvider()->getGatewayConfig()->getFactoryName();
        if ($factoryName !== 'powerpay') {
            return;
        }

        $powerpay = $this->payum->getGateway('powerpay');
        $powerpay->execute(new Cancel($payment));
    }
}
