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

use CoreShop\Component\Core\Model\PaymentInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\CreditAmount;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Security\TokenInterface;

final class RefundPaymentExtension extends AbstractExtension implements ExtensionInterface
{
    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        $previousActionClassName = is_object($action) ? get_class($action) : '';

        if (false === stripos($previousActionClassName, 'CreditAmountAction')) {
            return;
        }

        $request = $context->getRequest();
        if (!$request instanceof CreditAmount) {
            return;
        }

        $paymentEntity = null;
        if ($request->getToken() instanceof TokenInterface) {
            $paymentId = $request->getToken()->getDetails()->getId();
            $paymentEntity = $this->assertCoreShopPaymentById($paymentId);
        } elseif ($request->getFirstModel() instanceof Payment) {
            $paymentEntity = $this->assertCoreShopPayment($request->getFirstModel());
        } elseif ($request->getFirstModel() instanceof PaymentInterface) {
            $paymentEntity = $request->getFirstModel();
        }

        if (!$paymentEntity instanceof PaymentInterface) {
            return;
        }

        $result = $request->getResult();

        if (empty($result['credit_response_code'])) {
            return;
        }

        if ($result['credit_response_code'] === '00') {

            $description = '';
            if (is_array($result)) {
                foreach ($result as $lineTitle => $lineValue) {
                    $description .= '<strong>' . $lineTitle . ':</strong> ' . $lineValue . '<br>';
                }
            }

            $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay Payment successfully refunded (via credit)', $description);

            return;
        }

        $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay refunding error. Response Code: ' . $result['credit_response_code']);

    }
}
