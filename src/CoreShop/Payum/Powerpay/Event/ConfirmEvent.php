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

use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Payment\Model\Payment;
use CoreShop\Component\Payment\Model\PaymentInterface;
use CoreShop\Component\Payment\Repository\PaymentRepositoryInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Confirm;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;

class ConfirmEvent
{
    /**
     * @var bool
     */
    protected $processing = false;

    /**
     * @var Payum
     */
    protected $payum;

    /**
     * @var PaymentRepositoryInterface
     */
    protected $paymentRepository;

    /**
     * ConfirmEvent constructor.
     *
     * @param Payum                      $payum
     * @param PaymentRepositoryInterface $paymentRepository
     */
    public function __construct(Payum $payum, PaymentRepositoryInterface $paymentRepository)
    {
        $this->payum = $payum;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param OrderInterface $order
     *
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function confirmByOrder(OrderInterface $order)
    {
        $payments = $this->paymentRepository->findForPayable($order);

        $payment = null;
        /** @var PaymentInterface $orderPayment */
        foreach ($payments as $orderPayment) {
            if ($orderPayment->getState() !== Payment::STATE_PROCESSING) {
                continue;
            }
            $gatewayConfig = $orderPayment->getPaymentProvider()->getGatewayConfig();
            $factoryName = $gatewayConfig->getFactoryName();
            if ($factoryName === 'powerpay') {
                $payment = $orderPayment;
                break;
            }
        }

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $config = $gatewayConfig->getConfig();
        if ($config['confirmationMethod'] !== 'shipped') {
            return;
        }

        if ($this->processing === true) {
            return;
        }

        $this->processing = true;
        $powerpay = $this->payum->getGateway('powerpay');
        $powerpay->execute(new Confirm($payment));
        $powerpay->execute($status = new GetHumanStatus($payment));
    }

    /**
     * @param PaymentInterface $payment
     *
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function confirmByPayment(PaymentInterface $payment)
    {
        if ($payment->getState() !== Payment::STATE_PROCESSING) {
            return;
        }

        $factoryName = $payment->getPaymentProvider()->getGatewayConfig()->getFactoryName();
        if ($factoryName !== 'powerpay') {
            return;
        }

        if ($this->processing === true) {
            return;
        }

        $this->processing = true;
        $powerpay = $this->payum->getGateway('powerpay');
        $powerpay->execute(new Confirm($payment));
    }
}