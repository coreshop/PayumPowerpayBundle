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

use CoreShop\Component\Core\Model\Country;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Order\Model\OrderInterface;
use DachcomDigital\Payum\Powerpay\Request\Api\Transformer\CustomerTransformer;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;

final class CustomerTransformerExtension implements ExtensionInterface
{
    /**
     * @var array
     */
    protected $validLanguages = ['en', 'de', 'fr', 'it'];

    /**
     * @param Context $context
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();

        $previousActionClassName = get_class($action);
        if (false === stripos($previousActionClassName, 'CustomerTransformer')) {
            return;
        }

        /** @var CustomerTransformer $request */
        $request = $context->getRequest();
        if (false === $request instanceof CustomerTransformer) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        if (false === $payment instanceof PaymentInterface) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $request->setBirthDate($details['birthdate']);

        $this->setLanguage($order, $request);
        $this->setAddressData($order, $request);

    }

    /**
     * @param OrderInterface $order
     * @param CustomerTransformer $request
     */
    private function setLanguage($order, $request)
    {
        $defaultLanguage = 'en';
        $gatewayOrderLanguage = $defaultLanguage;

        if (!empty($order->getLocaleCode())) {
            $orderLanguage = $order->getLocaleCode();
            if (strpos($orderLanguage, '_') !== false) {
                $orderLanguage = explode('_', $orderLanguage);
                $gatewayOrderLanguage = $orderLanguage[0];
            } else {
                $gatewayOrderLanguage = $orderLanguage;
            }
        }

        if (!in_array($gatewayOrderLanguage, $this->validLanguages)) {
            $gatewayOrderLanguage = $defaultLanguage;
        }

        $request->setLanguage(strtolower($gatewayOrderLanguage));

    }

    /**
     * @param OrderInterface $order
     * @param CustomerTransformer $request
     */
    private function setAddressData($order, $request)
    {
        $customer = $order->getCustomer();
        $address = $order->getInvoiceAddress();
        /** @var Country $country */
        $country = $address->getCountry();

        $request->setGender($customer->getGender());
        $request->setEmail($customer->getEmail());
        $request->setFirstName($address->getFirstname());
        $request->setLastName($address->getLastName());
        $request->setStreet($address->getStreet() . ' ' . $address->getNumber());
        $request->setCity($address->getCity());
        $request->setZip($address->getPostcode());
        $request->setPhoneNumber((string) $address->getPhoneNumber());
        $request->setCountry($country->getIsoCode());
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
