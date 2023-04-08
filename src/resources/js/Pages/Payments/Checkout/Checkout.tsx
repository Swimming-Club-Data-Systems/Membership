import React, { useEffect, useState } from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import {
    Elements,
    PaymentElement,
    PaymentRequestButtonElement,
    useElements,
    useStripe,
} from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import Alert from "@/Components/Alert";
import Select from "@/Components/Form/Select";
import Card from "@/Components/Card";
import Table from "@/Components/Table";
import { router, usePage } from "@inertiajs/react";

type Props = {
    id: number;
    stripe_publishable_key: string;
    client_secret: string;
    stripe_account: string;
    country: string;
    currency: string;
    total: number;
    payment_methods: object[];
    return_url: string;
    formatted_total: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    customer_address: {
        line1: string;
        line2: string;
        city: string;
        country: string;
        postal_code: string;
    };
};

const CheckoutForm: React.FC<Props> = (props: Props) => {
    const stripe = useStripe();
    const elements = useElements();

    const [submitting, setSubmitting] = useState(false);
    const [paymentRequest, setPaymentRequest] = useState(null);

    const paymentMethods = [
        {
            stripe_id: "n/a",
            description: "Choose a saved payment method",
        },
        ...props.payment_methods,
    ];

    useEffect(() => {
        if (stripe) {
            const pr = stripe.paymentRequest({
                country: props.country,
                currency: props.currency,
                total: {
                    label: "Total",
                    amount: props.total,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            // Check the availability of the Payment Request API.
            pr.canMakePayment().then((result) => {
                if (result) {
                    setPaymentRequest(pr);
                }
            });
        }
    }, [stripe]);

    const [alert, setAlert] = useState(null);

    const handleStripeSubmit = async (values, formik) => {
        if (!stripe || !elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        let alertName = "";

        setSubmitting(true);

        let result;
        if (values.payment_method) {
            alertName = "saved_cards";
            // Try to charge the specified payment method
            result = await stripe.confirmCardPayment(props.client_secret, {
                payment_method: values.payment_method,
            });
        } else {
            alertName = "payment_element";
            result = await stripe.confirmPayment({
                //`Elements` instance that was used to create the Payment Element
                elements,
                confirmParams: {
                    return_url: props.return_url,
                },
                redirect: "if_required",
            });
        }

        if (result.error) {
            // Show error to your customer (for example, payment details incomplete)
            let message = "Please try another payment method.";

            if (
                result.error.type === "card_error" ||
                result.error.type === "validation_error"
            ) {
                message = result.error.message;
            }

            setAlert({
                variant: "error",
                title: "Payment failed",
                message: message,
                name: alertName,
            });

            setSubmitting(false);
            formik.setSubmitting(false);
        } else if (result.paymentIntent) {
            // We need to handle redirects if the payment was successful
            // Doing it this way allows us to use the Inertia SPA routing rather than a reload
            switch (result.paymentIntent.status) {
                case "succeeded":
                    // setMessage('Success! Payment received.');
                    router.visit(route("payments.checkout.success", props.id), {
                        replace: true,
                    });
                    break;

                case "processing":
                    router.visit(route("payments.checkout.success", props.id), {
                        replace: true,
                    });
                    break;

                case "requires_payment_method":
                    setAlert({
                        variant: "error",
                        title: "Payment failed",
                        message: "Please try another payment method.",
                        name: alertName,
                    });
                    break;

                default:
                    setAlert({
                        variant: "error",
                        title: "Something went wrong",
                        message: "Please try again.",
                        name: alertName,
                    });
                    // setMessage('Something went wrong.');
                    break;
            }
        } else {
            setSubmitting(false);
            formik.setSubmitting(false);
            // Your customer will be redirected to your `return_url`. For some payment
            // methods like iDEAL, your customer will be redirected to an intermediate
            // site first to authorize the payment, then redirected to the `return_url`.
        }
    };

    return (
        <>
            <div className="grid grid-cols-12">
                <div className="grid col-span-6 gap-4">
                    {paymentRequest && (
                        <>
                            <PaymentRequestButtonElement
                                options={{ paymentRequest }}
                            />

                            <div className="relative mb-4">
                                <div className="absolute inset-0 flex items-center">
                                    <div className="w-full border-t border-gray-300" />
                                </div>
                                <div className="relative flex justify-center text-sm">
                                    <span className="bg-gray-100 px-2 text-gray-500">
                                        Or
                                    </span>
                                </div>
                            </div>
                        </>
                    )}

                    <Form
                        initialValues={{
                            payment_method: "n/a",
                        }}
                        validationSchema={yup.object({
                            payment_method: yup
                                .string()
                                .required()
                                .test({
                                    name: "test_valid_pm",
                                    test: (value) => value !== "n/a",
                                    message: "Please select a payment method.",
                                }),
                        })}
                        onSubmit={handleStripeSubmit}
                        submitTitle={`Pay ${props.formatted_total} Now`}
                        hideClear
                        hideDefaultButtons
                        disabled={submitting}
                    >
                        <div className="grid gap-4">
                            <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                Use a saved payment method
                            </h2>
                            {alert && alert.name === "saved_cards" && (
                                <Alert
                                    title={alert.title}
                                    variant={alert.variant}
                                >
                                    <p>{alert.message}</p>
                                </Alert>
                            )}

                            <Select
                                name="payment_method"
                                options={paymentMethods.map(
                                    (payment_method) => {
                                        return {
                                            disabled:
                                                payment_method.stripe_id ===
                                                "n/a",
                                            key: payment_method.stripe_id,
                                            name: payment_method.description,
                                        };
                                    }
                                )}
                                label="Choose a saved payment method"
                            />

                            <SubmissionButtons />
                        </div>
                    </Form>

                    <div className="relative mb-4">
                        <div className="absolute inset-0 flex items-center">
                            <div className="w-full border-t border-gray-300" />
                        </div>
                        <div className="relative flex justify-center text-sm">
                            <span className="bg-gray-100 px-2 text-gray-500">
                                Or
                            </span>
                        </div>
                    </div>

                    <Form
                        initialValues={{}}
                        validationSchema={yup.object({})}
                        onSubmit={handleStripeSubmit}
                        submitTitle={`Pay ${props.formatted_total} Now`}
                        hideClear
                        hideDefaultButtons
                        disabled={submitting}
                    >
                        <div className="grid gap-4">
                            <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                                Use a new or other payment method
                            </h2>
                            {alert && alert.name === "payment_element" && (
                                <Alert
                                    title={alert.title}
                                    variant={alert.variant}
                                >
                                    <p>{alert.message}</p>
                                </Alert>
                            )}

                            <PaymentElement
                                options={{
                                    layout: "accordion",
                                    defaultValues: {
                                        billingDetails: {
                                            name: props.customer_name,
                                            email: props.customer_email,
                                            phone: props.customer_phone,
                                            address: {
                                                line1: props.customer_address
                                                    .line1,
                                                line2: props.customer_address
                                                    .line2,
                                                city: props.customer_address
                                                    .city,
                                                country:
                                                    props.customer_address
                                                        .country,
                                                postal_code:
                                                    props.customer_address
                                                        .postal_code,
                                            },
                                        },
                                    },
                                    business: {
                                        name: usePage().props?.tenant?.name,
                                    },
                                    paymentMethodOrder: ["card", "bacs_debit"],
                                    wallets: {
                                        // We use a separate Payment Request button at the top of the page
                                        applePay: "never",
                                        googlePay: "never",
                                    },
                                }}
                            />

                            <SubmissionButtons />
                        </div>
                    </Form>
                </div>

                <div className="col-span-5 col-start-8">
                    <div className="grid gap-4">
                        <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight">
                            Payment Summary
                        </h2>
                        <Card>
                            <Table
                                data={props.lines}
                                columns={[
                                    {
                                        headerName: "Description",
                                        field: "description",
                                    },
                                    {
                                        headerName: "Quantity",
                                        field: "quantity",
                                    },
                                    {
                                        headerName: "Total",
                                        field: "formatted_amount",
                                    },
                                ]}
                            />
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
};

const Checkout: Layout<Props> = (props: Props) => {
    const [stripe, setStripe] = useState(null);

    const appearance = {
        theme: "stripe",
        variables: {
            fontSizeBase: "1rem",
            fontSizeXl: "1.25rem",
            fontSizeLg: "1.125rem",
            fontSizeSm: "0.875rem",
            fontSizeXs: "0.75rem",
            fontSize2Xs: "0.75rem",
            fontSize3Xs: "0.75rem",
            fontLineHeight: "1.25rem",
            fontFeatureSettings: '"cv11", "ss01"',
            colorPrimary: "#4f46e5",
            colorBackground: "#ffffff",
            colorText: "#111827",
            colorDanger: "#7f1d1d",
            fontFamily:
                '"Inter var", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
            spacingUnit: "4px",
            spacingGridColumn: "1rem",
            spacingGridRow: "1rem",
            borderRadius: "0.5rem",
            // See all possible variables below
        },
        rules: {
            ".Accordion": {
                boxShadow:
                    "rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.1) 0px 1px 2px -1px",
            },
            ".AccordionItem": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Action": {
                fontSize: "var(--fontSizeSm)",
            },
            ".BlockAction": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Button": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Checkbox": {
                fontSize: "var(--fontSizeSm)",
            },
            ".CheckboxLabel": {
                fontSize: "var(--fontSizeSm)",
            },
            ".CodeInput": {
                fontSize: "var(--fontSizeSm)",
            },
            ".DropdownItem": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Error": {
                fontSize: "var(--fontSizeSm)",
                color: "#dc2626",
                marginTop: "0.5rem",
            },
            ".Input": {
                fontSize: "var(--fontSizeSm)",
                paddingTop: "0.5rem",
                paddingRight: "0.75rem",
                paddingBottom: "0.5rem",
                paddingLeft: "0.75rem",
                borderRadius: "0.375rem",
                lineHeight: "1.25rem",
                borderColor: "#d1d5db",
                boxShadow:
                    "rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.05) 0px 1px 2px 0px",
            },
            ".Input:focus": {
                borderColor: "var(--colorPrimary)",
                outline: "1px solid var(--colorPrimary)",
                outlineColor: "var(--colorPrimary)",
                boxShadow: "none",
            },
            ".Input--invalid": {
                borderColor: "#ef4444",
                boxShadow: "none",
                // outline: "1px solid #ef4444",
                // outlineColor: "#ef4444",
            },
            ".Input--empty": {
                // borderColor: "#ef4444",
                // outline: "1px solid #ef4444",
                // outlineColor: "#ef4444",
            },
            ".Input--invalid:focus": {
                borderColor: "#ef4444",
                outline: "1px solid #ef4444",
                outlineColor: "#ef4444",
            },
            ".Input--empty:focus": {
                // borderColor: "#ef4444",
                // outline: "1px solid #ef4444",
                // outlineColor: "#ef4444",
            },
            ".Label": {
                fontSize: "var(--fontSizeSm)",
                fontWeight: "500",
                color: "#374151",
                marginBottom: "0.25rem",
            },
            ".Link": {
                fontSize: "var(--fontSizeSm)",
            },
            ".MenuAction": {
                fontSize: "var(--fontSizeSm)",
            },
            ".PickerAction": {
                fontSize: "var(--fontSizeSm)",
            },
            ".PickerItem": {
                fontSize: "var(--fontSizeSm)",
            },
            ".RedirectText": {
                fontSize: "var(--fontSizeSm)",
            },
            ".SecondaryLink": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Switch": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Tab": {
                fontSize: "var(--fontSizeSm)",
            },
            ".TabLabel": {
                fontSize: "var(--fontSizeSm)",
            },
            ".TermsLink": {
                fontSize: "var(--fontSizeSm)",
            },
            ".TermsText": {
                fontSize: "var(--fontSizeSm)",
            },
            ".Text": {
                fontSize: "var(--fontSizeSm)",
            },
            // AccordionItem, Action, BlockAction, Button, Checkbox, CheckboxLabel, CodeInput, DropdownItem, Error, Input, Label, Link, MenuAction, PickerAction, PickerItem, RedirectText, SecondaryLink, Switch, Tab, TabLabel, TermsLink, TermsText, Text
            ".p-AccordionButton": {
                fontSize: "var(--fontSizeSm)",
            },
            ".p-AccordionHeader": {
                fontSize: "var(--fontSizeSm)",
            },
        },
    };
    const options = {
        clientSecret: props.client_secret,
        appearance,
        fonts: [
            {
                cssSrc: "https://rsms.me/inter/inter.css",
            },
        ],
    };

    useEffect(() => {
        loadStripe(props.stripe_publishable_key, {
            stripeAccount: props.stripe_account,
        }).then((value) => {
            if (value) {
                setStripe(value);
            }
        });
    }, []);

    return (
        <>
            <Head
                title={`Checkout`}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    // { name: "Checkout", route: "payments.statements.index" },
                    // {
                    //     name: `#${props.id}`,
                    //     route: "payments.statements.show",
                    //     routeParams: props.id,
                    // },
                ]}
            />

            <MainHeader
                title={`SCDS Checkout`}
                subtitle="Pay for stuff"
            ></MainHeader>

            {stripe && (
                <Elements stripe={stripe} options={options}>
                    <CheckoutForm {...props} />
                </Elements>
            )}
        </>
    );
};

Checkout.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Checkout;
