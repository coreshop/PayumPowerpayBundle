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

use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\Country;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\TransactionTransformer;
use DachcomDigital\Payum\Powerpay\Transaction\Transaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\Payment;

final class ConvertPaymentExtension extends AbstractExtension implements ExtensionInterface
{
    protected array $validLanguages = ['en', 'de', 'fr', 'it'];

    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        $request = $context->getRequest();

        $previousActionClassName = is_object($action) ? get_class($action) : '';

        if (false === stripos($previousActionClassName, 'TransactionTransformer')) {
            return;
        }

        if (!$request instanceof TransactionTransformer) {
            return;
        }

        $payment = $request->getFirstModel();
        if (!$payment instanceof Payment) {
            return;
        }

        $paymentEntity = $this->assertCoreShopPayment($request->getFirstModel());
        if (!$paymentEntity instanceof PaymentInterface) {
            return;
        }

        $order = $paymentEntity->getOrder();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $transaction = $request->getTransaction();

        $transaction->setBirthDate($details['birthdate']);

        $this->setLanguage($order, $transaction);
        $this->setAddressData($order, $transaction);
    }

    private function setLanguage(OrderInterface $order, Transaction $transaction): void
    {
        $defaultLanguage = 'en';
        $gatewayOrderLanguage = $defaultLanguage;

        if (!empty($order->getLocaleCode())) {
            $orderLanguage = $order->getLocaleCode();
            if (str_contains($orderLanguage, '_')) {
                $orderLanguage = explode('_', $orderLanguage);
                $gatewayOrderLanguage = $orderLanguage[0];
            } else {
                $gatewayOrderLanguage = $orderLanguage;
            }
        }

        if (!in_array($gatewayOrderLanguage, $this->validLanguages, true)) {
            $gatewayOrderLanguage = $defaultLanguage;
        }

        $transaction->setLanguage(strtolower($gatewayOrderLanguage));
    }

    private function setAddressData(OrderInterface $order, Transaction $transaction): void
    {
        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();
        /** @var AddressInterface $address */
        $address = $order->getInvoiceAddress();
        /** @var Country $country */
        $country = $address->getCountry();

        $transaction->setGender($customer->getGender());
        $transaction->setEmail($customer->getEmail());
        $transaction->setFirstName($address->getFirstname());
        $transaction->setLastName($address->getLastName());
        $transaction->setStreet(sprintf('%s %s', $address->getStreet(), $address->getNumber()));
        $transaction->setCity($address->getCity());
        $transaction->setZip($address->getPostcode());
        $transaction->setPhoneNumber((string) $address->getPhoneNumber());
        $transaction->setCountry($country->getIsoCode());
    }
}
