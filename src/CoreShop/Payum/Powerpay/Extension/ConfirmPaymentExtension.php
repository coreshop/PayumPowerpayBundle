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

use CoreShop\Bundle\WorkflowBundle\History\HistoryLoggerInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Payment\Repository\PaymentRepositoryInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Confirm;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Security\TokenInterface;

final class ConfirmPaymentExtension implements ExtensionInterface
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var HistoryLoggerInterface
     */
    private $orderHistoryLogger;

    /**
     * @param PaymentRepositoryInterface $paymentRepository
     * @param HistoryLoggerInterface         $orderHistoryLogger
     */
    public function __construct(PaymentRepositoryInterface $paymentRepository, HistoryLoggerInterface $orderHistoryLogger)
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

        if (false === stripos($previousActionClassName, 'ConfirmAction')) {
            return;
        }

        /** @var Confirm $request */
        $request = $context->getRequest();
        if (false === $request instanceof Confirm) {
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

        $result = $request->getResult();

        if (isset($result['skipped']) && $result['skipped'] === true) {
            $this->orderHistoryLogger->log($payment->getOrder(), 'PowerPay Payment skipped. Reason: ' . $result['skipped_reason']);
        } elseif (isset($result['response_code'])) {
            $this->orderHistoryLogger->log($payment->getOrder(), 'PowerPay error. Response Code: ' . $result['response_code']);
        } else {
            $description = '';
            if (is_array($result)) {
                foreach ($result as $lineTitle => $lineValue) {
                    $description .= '<strong>' . $lineTitle . ':</strong> ' . $lineValue . '<br>';
                }
            }

            $this->orderHistoryLogger->log($payment->getOrder(), 'PowerPay Payment successfully submitted', $description);
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
