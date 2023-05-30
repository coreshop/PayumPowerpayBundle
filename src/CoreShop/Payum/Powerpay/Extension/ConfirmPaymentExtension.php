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
use DachcomDigital\Payum\Powerpay\Request\Api\Confirm;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Security\TokenInterface;

final class ConfirmPaymentExtension extends AbstractExtension implements ExtensionInterface
{
    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        $previousActionClassName = is_object($action) ? get_class($action) : '';

        if (false === stripos($previousActionClassName, 'ConfirmAction')) {
            return;
        }

        $request = $context->getRequest();
        if (!$request instanceof Confirm) {
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

        $detail = $request->getModel();
        $result = $request->getResult();

        if ($detail instanceof \ArrayObject && isset($detail['error_message'])) {
            $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay error. ' . $detail['error_message']);

            return;
        }

        if (isset($result['skipped']) && $result['skipped'] === true) {
            $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay payment skipped. Reason: ' . $result['skipped_reason']);
        } elseif (isset($result['response_code'])) {
            $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay error. Response Code: ' . $result['response_code']);
        } else {
            $description = '';
            if (is_array($result)) {
                foreach ($result as $lineTitle => $lineValue) {
                    $description .= '<strong>' . $lineTitle . ':</strong> ' . $lineValue . '<br>';
                }
            }

            $this->orderHistoryLogger->log($paymentEntity->getOrder(), 'PowerPay payment successfully submitted', $description);
        }
    }
}
