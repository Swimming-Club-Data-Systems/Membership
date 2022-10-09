import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import Link from "@/Components/Link";
import FlashAlert from "@/Components/FlashAlert";

const Index = (props) => {
    return (
        <>
            <Head title={`Stripe Account - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Stripe Account
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Information about the tenant organisation and
                            settings
                        </p>
                    </div>

                    <FlashAlert className="mb-4" />

                    {props.stripe_account && (
                        <div>
                            <p className="text-sm mb-3">
                                Your Stripe account ({props.stripe_account}) is
                                currently connected.
                            </p>

                            <p className="text-sm">
                                You can view information about your Stripe
                                account{" "}
                                <Link
                                    href="https://dashboard.stripe.com/"
                                    external
                                >
                                    in the Stripe Dashboard
                                </Link>
                                .
                            </p>
                        </div>
                    )}

                    {props.stripe_account && (
                        <div>
                            <p className="text-sm mb-3">
                                The membership system integrates with{" "}
                                <Link href="https://stripe.com/" external>
                                    Stripe
                                </Link>{" "}
                                allowing you to accept Direct Debit, card
                                payments, and other local and international
                                payment methods.
                            </p>

                            <p className="text-sm mb-3">
                                <Link
                                    href={route(
                                        "central.tenants.setup_stripe",
                                        props.id
                                    )}
                                >
                                    Create an account with Stripe and SCDS
                                </Link>
                                .
                            </p>

                            <p className="text-sm">
                                We&apos;ll send you to Stripe and ask you to
                                sign in or create an account. Find out{" "}
                                <Link
                                    href="https://stripe.com/gb/payments"
                                    external
                                >
                                    more about Stripe
                                </Link>{" "}
                                and{" "}
                                <Link
                                    href="https://stripe.com/gb/pricing"
                                    external
                                >
                                    their pricing
                                </Link>
                                .
                            </p>
                        </div>
                    )}
                </Card>
            </div>
        </>
    );
};

Index.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Index;
