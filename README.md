# CoreShop Powerpay Payum Connector
This Bundle activates the Powerpay PaymentGateway in CoreShop.
It requires the [dachcom-digital/payum-powerpay](https://github.com/dachcom-digital/payum-powerpay) repository which will be installed automatically.

## Requirements
CoreShop >= 2.0.0

## Installation

#### 1. Composer

```json
    "coreshop/payum-powerpay-bundle": "~1.0.0"
```

#### 2. Activate
Enable the Bundle in Pimcore Extension Manager

#### 3. Setup
Go to Coreshop -> PaymentProvider and add a new Provider. Choose `powerpay` from `type` and fill out the required fields.

## How-To

### Confirm Payment
To confirm a payment you need to create a shipment and apply the `ship` transition.
All items from the PowerPay Payment Item will be transmitted.
You'll see a success log in the order history log section.

### Cancel Payment
If you need to cancel a payment just cancel the payment itself.
You'll see a success log in the order history log section.
**Note**: This works only if a payment hasn't confirmed yet.

## Upgrade Notes
- v1.0.3: transmit phoneNumber