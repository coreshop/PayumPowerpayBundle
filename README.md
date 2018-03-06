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
