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
use Payum\Core\Extension\Context;
use Payum\Core\Model\Payment;

abstract class AbstractExtension
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected HistoryLoggerInterface $orderHistoryLogger
    ) {
    }

    protected function assertCoreShopPayment(Payment $payment): ?PaymentInterface
    {
        return $this->paymentRepository->createQueryBuilder('p')
            ->where('p.number = :orderNumber')
            ->setParameter('orderNumber', $payment->getNumber())
            ->getQuery()
            ->getSingleResult();
    }

    protected function assertCoreShopPaymentById(int $id): ?PaymentInterface
    {
        return $this->paymentRepository->find($id);
    }

    public function onPreExecute(Context $context): void
    {

    }

    public function onExecute(Context $context): void
    {
    }
}
