<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Payum\PowerpayBundle\Event;

use CoreShop\Component\Payment\Model\Payment;
use CoreShop\Component\Payment\Model\PaymentInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\CreditAmount;
use Payum\Core\Payum;

class RefundEvent
{
    /**
     * @var Payum
     */
    protected $payum;

    /**
     * ConfirmEvent constructor.
     *
     * @param Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @param PaymentInterface $payment
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function refund(PaymentInterface $payment)
    {
        if ($payment->getState() !== Payment::STATE_COMPLETED) {
            return;
        }

        $factoryName = $payment->getPaymentProvider()->getGatewayConfig()->getFactoryName();
        if ($factoryName !== 'powerpay') {
            return;
        }

        $powerpay = $this->payum->getGateway('powerpay');
        $powerpay->execute(new CreditAmount($payment));
    }
}