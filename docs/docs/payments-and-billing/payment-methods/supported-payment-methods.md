# Supported Payment Methods

Payments and Billing (V2)
supports [all payment methods that are supported by Stripe](https://stripe.com/gb/payments/payment-methods).

:::info

Our legacy payment systems only support Card and BACS Direct Debit payment methods. To support additional methods,
you'll need to opt in and move to V2. All clubs will be automatically
migrated to Payments and Billing (V2) in due time.

:::

Stripe however charge varying amounts, depending on the type of payment method used. As a result, we give you the
choice as to which methods to allow for different types of payment. You can also opt to use Automatic Payment Methods,
which let's Stripe determine appropriate payment methods based on the user's location and the charge currency.

Please note that all payment methods have specific requirements for their use and may contain additional restrictions
that you must comply with, such as marketing guidelines, additional prohibited and restricted businesses, and
information about handling disputes and refunds.

## Automatic Payment Methods

If you enable Automatic Payment Methods in the System Settings section of the Membership System, you can manage your
payment methods from the Stripe Dashboard. This allows Stripe to pull your payment method
preferences from the Dashboard to dynamically display the most relevant payment methods to your customers.

[Find out more at about Automatic Payment Methods in the Stripe Docs.](https://stripe.com/docs/payments/payment-methods/integration-options#using-automatic-payment-methods)

## Manual Payment Methods

SCDS recommends using Manual Payment Methods. This allows you to select the specific payment methods you'd like to
support for specific payment types in Membership System Settings.

SCDS lets you choose supported payment methods for the following types of payment:

- Galas
- Membership (Club and Swim England)
- Default
- Balance Top Up

You should consult [Stripe's Pricing Page](https://stripe.com/gb/pricing) and also consider what's best for your
customers when choosing which payment methods to enable.

## Recommended Payment Methods

SCDS recommends that you always accept:

- Card
- BACS Direct Debit

For payments from non-members, it's best to use card payments.