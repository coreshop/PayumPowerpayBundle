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
use CoreShop\Component\Payment\Repository\PaymentRepositoryInterface;
use DachcomDigital\Payum\Powerpay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use DachcomDigital\Payum\Powerpay\Request\Api\ReserveAmount;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FailedPaymentExtension extends AbstractExtension implements ExtensionInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        HistoryLoggerInterface $orderHistoryLogger,
        TranslatorInterface $translator
    ) {
        parent::__construct($paymentRepository, $orderHistoryLogger);

        $this->translator = $translator;
    }

    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        $previousActionClassName = is_object($action) ? get_class($action) : '';

        if (false === stripos($previousActionClassName, 'ReserveAmountAction')) {
            return;
        }

        $request = $context->getRequest();
        if (!$request instanceof ReserveAmount) {
            return;
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());
        if (!isset($details['credit_refusal_reason'])) {
            return;
        }

        if ($details['credit_refusal_reason'] === StatusAction::REFUSAL_REASON_UNKNOWN_ADDRESS ||
            $details['credit_refusal_reason'] === StatusAction::REFUSAL_REASON_OTHER) {
            $details['coreshop_payment_note'] = $this->translator->trans('powerpay.ui.failed_message');
        }

        $request->setModel($details);
    }
}
