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

use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Order\Repository\OrderRepositoryInterface;
use CoreShop\Component\Payment\Model\PaymentInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentExtension implements ExtensionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var array
     */
    protected $validLanguages = ['en', 'de', 'fr', 'it'];

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Context $context
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();

        $previousActionClassName = get_class($action);
        if (false === stripos($previousActionClassName, 'ConvertPaymentAction')) {
            return;
        }

        /** @var Convert $request */
        $request = $context->getRequest();
        if (false === $request instanceof Convert) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        if (false === $payment instanceof PaymentInterface) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($payment->getOrderId());

        $result = ArrayObject::ensureArrayObject($request->getResult());

        $result['language'] = $this->getOrderLanguage($order);
        $result['address'] = $this->getAddressData($order);

        $request->setResult((array)$result);

    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getOrderLanguage($order)
    {
        $defaultLanguage = 'en';
        $gatewayOrderLanguage = $defaultLanguage;

        if (!empty($order->getOrderLanguage())) {
            $orderLanguage = $order->getOrderLanguage();
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

        return strtolower($gatewayOrderLanguage);
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function getAddressData($order)
    {
        $customer = $order->getCustomer();
        $address = $order->getInvoiceAddress();
        /** @var \CoreShop\Component\Core\Model\Country $country */
        $country = $address->getCountry();

        $address = [
            'gender'    => $customer->getGender(),
            'email'     => $customer->getEmail(),
            'firstName' => $address->getFirstname(),
            'lastName'  => $address->getLastName(),
            'street'    => $address->getStreet(),
            'city'      => $address->getCity(),
            'zip'       => $address->getPostcode(),
            'country'   => $country->getIsoCode()
        ];

        return $address;
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
