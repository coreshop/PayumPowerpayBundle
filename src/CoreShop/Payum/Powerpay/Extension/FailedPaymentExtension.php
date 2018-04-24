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

use CoreShop\Component\Payment\Model\PaymentInterface;
use DachcomDigital\Payum\Powerpay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use DachcomDigital\Payum\Powerpay\Request\Api\ReserveAmount;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class FailedPaymentExtension implements ExtensionInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FailedPaymentExtension constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Context $context
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();
        $previousActionClassName = get_class($action);

        if (false === stripos($previousActionClassName, 'ReserveAmountAction')) {
            return;
        }

        /** @var ReserveAmount $request */
        $request = $context->getRequest();
        if (false === $request instanceof ReserveAmount) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getPayment();
        if (false === $payment instanceof PaymentInterface) {
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
