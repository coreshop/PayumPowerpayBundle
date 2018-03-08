# CoreShop Powerpay Payum Connector
This Bundle activates the Powerpay PaymentGateway in CoreShop.
It requires the [dachcom-digital/payum-powerpay](https://github.com/dachcom-digital/payum-powerpay) repository which will be installed automatically.

## Installation

#### 1. Composer

```json
    "coreshop/payum-powerpay-bundle": "dev-master"
```

#### 2. Activate
Enable the Bundle in Pimcore Extension Manager

#### 3. Setup
Go to Coreshop -> PaymentProvider and add a new Provider. Choose `powerpay` from `type` and fill out the required fields.

#### 4. CoreShop

Add Invoice State to allow Order Completion:

```yml
# app/config/config.yml
parameters:
    coreshop.workflow.include_invoice_state_to_complete_order: true
```

## How-To

### Confirm Payment
To confirm a payment you need to create an invoice and apply the `complete` transition.
All items from the PowerPay Payment Item will be transmitted. You'll see a success log in the order history log section.

### Cancel Payment
If you need to cancel a payment just cancel the payment itself. You'll see a success log in the order history log section.
**Note**: This works only if a payment hasn't confirmed yet.

