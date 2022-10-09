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
            <Head title={`Billing - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Billing
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Manage your payment methods, payments and
                            subscriptions.
                        </p>
                    </div>

                    <FlashAlert className="mb-4" />

                    <div>
                        <p className="text-sm mb-3">
                            <Link
                                href={route(
                                    "central.tenants.billing.add-method",
                                    props.id
                                )}
                            >
                                Add a payment method
                            </Link>
                            .
                        </p>

                        <p className="text-sm">
                            <Link
                                href={route(
                                    "central.tenants.billing.portal",
                                    props.id
                                )}
                            >
                                View Customer Portal
                            </Link>
                            .
                        </p>
                    </div>
                </Card>

                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Payment methods
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Manage your payment methods.
                        </p>
                    </div>

                    <FlashAlert className="mb-4" bag="payment_method" />

                    {props.payment_method && (
                        <div>
                            <p className="text-sm">
                                Your default payment method is{" "}
                                {
                                    props.payment_method[
                                        props.payment_method.type
                                    ].last4
                                }
                                .
                            </p>
                        </div>
                    )}

                    {!props.payment_method && (
                        <div>
                            <p className="text-sm">
                                You have no default payment method set up.{" "}
                                <Link
                                    href={route(
                                        "central.tenants.billing.add-method",
                                        props.id
                                    )}
                                >
                                    Add a payment method
                                </Link>
                                .
                            </p>
                        </div>
                    )}
                </Card>

                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Invoices
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            View your invoice history.
                        </p>
                    </div>

                    <FlashAlert className="mb-4" bag="payment_method" />

                    {props.invoices.map((invoice) => {
                        return (
                            <div>
                                <p className="text-sm">{invoice.total}</p>
                            </div>
                        );
                    })}

                    {!props.payment_method && (
                        <div>
                            <p className="text-sm">
                                You have no default payment method set up.{" "}
                                <Link
                                    href={route(
                                        "central.tenants.billing.add-method",
                                        props.id
                                    )}
                                >
                                    Add a payment method
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
