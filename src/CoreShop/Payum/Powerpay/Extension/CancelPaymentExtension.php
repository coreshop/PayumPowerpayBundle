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

namespace CoreShop\Payum\PowerpayBundle\Extension;

use CoreShop\Bundle\OrderBundle\Workflow\OrderHistoryLogger;
use CoreShop\Component\Order\Repository\OrderRepositoryInterface;
use CoreShop\Component\Payment\Model\PaymentInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Cancel;
use DachcomDigital\Payum\Powerpay\Request\Api\Confirm;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;

final class CancelPaymentExtension implements ExtensionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var OrderHistoryLogger
     */
    private $orderHistoryLogger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderHistoryLogger       $orderHistoryLogger
     */
    public function __construct(OrderRepositoryInterface $orderRepository, OrderHistoryLogger $orderHistoryLogger)
    {
        $this->orderRepository = $orderRepository;
        $this->orderHistoryLogger = $orderHistoryLogger;
    }

    /**
     * @param Context $context
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();

        $previousActionClassName = get_class($action);

        if (false === stripos($previousActionClassName, 'CancelAction')) {
            return;
        }

        /** @var Confirm $request */
        $request = $context->getRequest();
        if (false === $request instanceof Cancel) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        if (false === $payment instanceof PaymentInterface) {
            return;
        }

        $orderId = $payment->getOrderId();
        $result = $request->getResult();

        if (isset($result['skipped']) && $result['skipped'] === true) {
            $this->orderHistoryLogger->log($orderId, 'PowerPay Payment cancellation skipped. Reason: ' . $result['skipped_reason']);
        } elseif (isset($result['response_code'])) {
            $this->orderHistoryLogger->log($orderId, 'PowerPay cancellation error. Response Code: ' . $result['response_code']);
        } else {
            $description = '';
            foreach($result as $lineTitle => $lineValue) {
                $description .= '<strong>' . $lineTitle . ':</strong> ' . $lineValue . '<br>';
            }

            $this->orderHistoryLogger->log($orderId, 'PowerPay Payment successfully cancelled', $description);
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
