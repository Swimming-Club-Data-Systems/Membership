import React, { ReactNode, useEffect, useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import BasicList from "@/Components/BasicList";
import { formatDate } from "@/Utils/date-utils";
import { DefinitionList } from "@/Components/DefinitionList";
import Link from "@/Components/Link";
import ButtonLink from "@/Components/ButtonLink";
import Alert from "@/Components/Alert";
import {
    Elements,
    ExpressCheckoutElement,
    useStripe,
    useElements,
} from "@stripe/react-stripe-js";
import {
    loadStripe,
    StripeElementsOptions,
    StripeExpressCheckoutElementOptions,
} from "@stripe/stripe-js";
import axios from "@/Utils/axios";
import { usePage } from "@inertiajs/react";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
        require_times: boolean;
        processing_fee_formatted: string;
        processing_fee: number;
    };
    paid: boolean;
    payable: boolean;
    id: string;
    first_name: string;
    last_name: string;
    email: string;
    entrants: {
        id: string;
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
        age: number;
        amount: number;
        amount_formatted: string;
    }[];
    tenant: {
        name: string;
    };
    stripe_publishable_key: string;
    stripe_account: string;
    amount: number;
    amount_formatted: string;
    currency: string;
};

type ExpressCheckoutProps = {
    /** Path to the JSON route for creating an intent and returning the client secret */
    createIntentRoute: string;
};

const ExpressCheckout = ({ createIntentRoute }: ExpressCheckoutProps) => {
    const stripe = useStripe();
    const elements = useElements();
    const [errorMessage, setErrorMessage] = useState<string>(null);
    const [hasExpressCheckoutOptions, setHasExpressCheckoutOptions] =
        useState(false);
    const { props: pageProps } = usePage();

    const expressCheckoutOptions: StripeExpressCheckoutElementOptions = {
        wallets: {
            applePay: "always",
            googlePay: "always",
        },
        buttonType: {
            applePay: "check-out",
            googlePay: "checkout",
            // paypal: "checkout",
        },
    };

    const onExpressCheckoutReady = ({ availablePaymentMethods }) => {
        if (!availablePaymentMethods) {
            // No buttons will show
        } else {
            // Optional: Animate in the Element
            setHasExpressCheckoutOptions(true);
        }
    };

    const onExpressCheckoutClick = ({ resolve }) => {
        const options = {
            business: {
                name: pageProps.tenant.name,
            },
            emailRequired: true,
        };
        resolve(options);
    };

    const onExpressCheckoutConfirm = async () => {
        if (!stripe) {
            // Stripe.js hasn't loaded yet.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        const { error: submitError } = await elements.submit();
        if (submitError) {
            setErrorMessage(submitError.message);
            return;
        }

        // Call the server
        // Create the PaymentIntent and obtain clientSecret

        try {
            const res = await axios.post(createIntentRoute);
            const { client_secret: clientSecret, return_url: returnUrl } =
                res.data;

            // Confirm the PaymentIntent using the details collected by the Express Checkout Element
            const { error } = await stripe.confirmPayment({
                // `elements` instance used to create the Express Checkout Element
                elements,
                // `clientSecret` from the created PaymentIntent
                clientSecret,
                confirmParams: {
                    return_url: returnUrl,
                },
            });

            if (error) {
                // This point is only reached if there's an immediate error when
                // confirming the payment. Show the error to your customer (for example, payment details incomplete)
                setErrorMessage(error.message);
            } else {
                // The payment UI automatically closes with a success animation.
                // Your customer is redirected to your `return_url`.
            }
        } catch (error) {
            console.error(error);
            if (error.response) {
                setErrorMessage(error.response.data.message);
            } else if (error.request) {
                setErrorMessage(
                    "An error occurred during a request to the SCDS server. You will not be charged. Please try again later."
                );
            } else {
                setErrorMessage(
                    "An unknown error occurred. You will not be charged. Please try again later."
                );
            }
        }
    };

    return (
        <>
            {errorMessage && (
                <Alert title="Error" variant="error">
                    {errorMessage}
                </Alert>
            )}

            <ExpressCheckoutElement
                onReady={onExpressCheckoutReady}
                onClick={onExpressCheckoutClick}
                onConfirm={onExpressCheckoutConfirm}
                options={expressCheckoutOptions}
            />

            {hasExpressCheckoutOptions && (
                <div className="relative mb-4">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="bg-white px-2 text-gray-500">Or</span>
                    </div>
                </div>
            )}
        </>
    );
};

const GuestEntryShow: Layout<Props> = (props: Props) => {
    const [stripe, setStripe] = useState(null);

    const options: StripeElementsOptions = {
        mode: "payment",
        amount: props.amount,
        currency: props.currency,
        appearance: {},
    };

    useEffect(() => {
        loadStripe(props.stripe_publishable_key, {
            stripeAccount: props.stripe_account,
        }).then((value) => {
            if (value) {
                setStripe(value);
            }
        });
    }, [props.stripe_account, props.stripe_publishable_key]);

    return (
        <>
            <Head
                title="Guest Entry"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    {
                        name: "Guest Entry",
                        route: "competitions.enter_as_guest.show",
                        routeParams: {
                            competition: props.competition.id,
                            header: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={"Manage your entries"}
                    subtitle={`Hi ${props.first_name}, you can enter, update details and pay from here.`}
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    {props.paid && (
                        <Alert title="Entries paid">
                            Thank you for paying for your entries. Should you
                            need to make any changes to your entry, please
                            contact {props.tenant.name}.
                        </Alert>
                    )}

                    <FlashAlert />

                    {!props.paid && (
                        <Card title="What to do?">
                            <div className="prose prose-sm">
                                <p>
                                    For each of your entrants, you will now need
                                    to select events to enter.{" "}
                                    {props.competition.require_times && (
                                        <>
                                            You will need to provide an entry
                                            time for each event you select. This
                                            will be used to seed competitors
                                            into heats.{" "}
                                        </>
                                    )}{" "}
                                    Press the link next to each entrant&apos;s
                                    name to get started.
                                </p>

                                <p>
                                    Once you have selected events for each
                                    entrant, you will be brought back to this
                                    page where you can proceed to payment. Your
                                    details will be deleted if you don't pay
                                    within 24 hours of starting your entry.
                                </p>

                                <p>
                                    If you need further assistance, please
                                    contact {props.tenant.name}.
                                </p>
                            </div>
                        </Card>
                    )}

                    <Card title="Your details">
                        <DefinitionList
                            verticalPadding={2}
                            items={[
                                {
                                    key: "name",
                                    term: "Name",
                                    definition: `${props.first_name} ${props.last_name}`,
                                },
                                {
                                    key: "email",
                                    term: "Email address",
                                    definition: props.email,
                                },
                            ]}
                        />
                    </Card>

                    <Card title="Entrants">
                        <BasicList
                            items={props.entrants.map((entrant) => {
                                return {
                                    id: entrant.id,
                                    content: (
                                        <div className="flex items-center justify-between text-sm">
                                            <div>
                                                <div>
                                                    {entrant.first_name}{" "}
                                                    {entrant.last_name} (
                                                    {entrant.age})
                                                </div>
                                                <div>
                                                    {formatDate(
                                                        entrant.date_of_birth
                                                    )}{" "}
                                                    - {entrant.amount_formatted}
                                                </div>
                                            </div>
                                            <div>
                                                <Link
                                                    href={route(
                                                        "competitions.enter_as_guest.edit_entry",
                                                        {
                                                            competition:
                                                                props
                                                                    .competition
                                                                    .id,
                                                            header: props.id,
                                                            entrant: entrant.id,
                                                        }
                                                    )}
                                                >
                                                    {props.paid
                                                        ? "View swims"
                                                        : "Select swims"}{" "}
                                                    <span aria-hidden="true">
                                                        {" "}
                                                        &rarr;
                                                    </span>
                                                </Link>
                                            </div>
                                        </div>
                                    ),
                                };
                            })}
                        ></BasicList>
                    </Card>

                    {props.payable && (
                        <Elements stripe={stripe} options={options}>
                            <Card title="Payment">
                                <div className="prose prose-sm">
                                    <p>Ready to pay?</p>

                                    <p>
                                        {props.competition.processing_fee && (
                                            <>
                                                A processing fee of{" "}
                                                {
                                                    props.competition
                                                        .processing_fee_formatted
                                                }{" "}
                                                per entrant applies to this
                                                competition.{" "}
                                            </>
                                        )}
                                        The total cost of your entries is{" "}
                                        {props.amount_formatted}.
                                    </p>

                                    <p>
                                        Once you pay, you&apos;ll no longer be
                                        able to amend your entry. If you need to
                                        make changes, you&apos;ll need to
                                        contact {props.tenant.name} directly.
                                    </p>
                                </div>

                                <ExpressCheckout
                                    createIntentRoute={route(
                                        "competitions.enter_as_guest.express_pay",
                                        {
                                            competition: props.competition.id,
                                            header: props.id,
                                        }
                                    )}
                                />

                                <div className="d-grid">
                                    <ButtonLink
                                        href={route(
                                            "competitions.enter_as_guest.pay",
                                            {
                                                competition:
                                                    props.competition.id,
                                                header: props.id,
                                            }
                                        )}
                                        className="py-[11px] w-full"
                                    >
                                        Proceed to Checkout
                                    </ButtonLink>
                                </div>
                            </Card>
                        </Elements>
                    )}
                </div>
            </Container>
        </>
    );
};

GuestEntryShow.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default GuestEntryShow;
