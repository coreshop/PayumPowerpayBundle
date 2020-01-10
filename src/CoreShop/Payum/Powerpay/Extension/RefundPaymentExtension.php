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

namespace CoreShop\Payum\PowerpayBundle\Extension;

use CoreShop\Bundle\OrderBundle\Workflow\OrderHistoryLogger;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Payment\Repository\PaymentRepositoryInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Confirm;
use DachcomDigital\Payum\Powerpay\Request\Api\CreditAmount;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Security\TokenInterface;

final class RefundPaymentExtension implements ExtensionInterface
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var OrderHistoryLogger
     */
    private $orderHistoryLogger;

    /**
     * @param PaymentRepositoryInterface $paymentRepository
     * @param OrderHistoryLogger         $orderHistoryLogger
     */
    public function __construct(PaymentRepositoryInterface $paymentRepository, OrderHistoryLogger $orderHistoryLogger)
    {
        $this->paymentRepository = $paymentRepository;
        $this->orderHistoryLogger = $orderHistoryLogger;
    }

    /**
     * @param Context $context
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();

        $previousActionClassName = get_class($action);

        if (false === stripos($previousActionClassName, 'CreditAmountAction')) {
            return;
        }

        /** @var Confirm $request */
        $request = $context->getRequest();
        if (false === $request instanceof CreditAmount) {
            return;
        }

        $payment = false;
        if ($request->getToken() instanceof TokenInterface) {
            $paymentId = $request->getToken()->getDetails()->getId();
            $payment = $this->paymentRepository->find($paymentId);
        } elseif ($request->getFirstModel() instanceof PaymentInterface) {
            $payment = $request->getFirstModel();
        }

        /** @var PaymentInterface $payment */
        if (false === $payment instanceof PaymentInterface) {
            return;
        }

        $orderId = $payment->getOrder()->getId();
        $result = $request->getResult();

        if (isset($result['credit_response_code']) && !empty($result['credit_response_code'])) {
            if ($result['credit_response_code'] === '00') {
                $description = '';
                foreach ($result as $lineTitle => $lineValue) {
                    $description .= '<strong>' . $lineTitle . ':</strong> ' . $lineValue . '<br>';
                }

                $this->orderHistoryLogger->log($orderId, 'PowerPay Payment successfully refunded (via credit)', $description);

            } else {
                $this->orderHistoryLogger->log($orderId, 'PowerPay refunding error. Response Code: ' . $result['credit_response_code']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute(Context $context)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function onExecute(Context $context)
    {
    }
}
