import React, { useState } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import Link from "@/Components/Link";
import FlashAlert from "@/Components/FlashAlert";
import Button from "@/Components/Button";
import ButtonLink from "@/Components/ButtonLink";
import BasicList from "@/Components/BasicList";
import Modal from "@/Components/Modal";
import { Inertia } from "@inertiajs/inertia";
import { fromUnixTime } from "date-fns";
import Table from "@/Components/Table";

const Index = (props) => {
    const [showPaymentMethodDeleteModal, setShowPaymentMethodDeleteModal] =
        useState(false);
    const [paymentMethodDeleteModalData, setPaymentMethodDeleteModalData] =
        useState(null);
    const [showPaymentMethodDefaultModal, setShowPaymentMethodDefaultModal] =
        useState(false);
    const [paymentMethodDefaultModalData, setPaymentMethodDefaultModalData] =
        useState(null);

    const deletePaymentMethod = async () => {
        Inertia.delete(
            route("central.tenants.billing.update_payment_method", [
                props.id,
                paymentMethodDeleteModalData.id,
            ]),
            {
                only: ["payment_methods", "flash"],
                preserveScroll: true,
                preserveState: true,
                onFinish: (page) => {
                    setShowPaymentMethodDeleteModal(false);
                },
            }
        );
    };

    const setDefaultPaymentMethod = () => {
        Inertia.put(
            route("central.tenants.billing.update_payment_method", [
                props.id,
                paymentMethodDefaultModalData.id,
            ]),
            {
                set_default: true,
            },
            {
                only: ["payment_methods", "flash"],
                preserveScroll: true,
                preserveState: true,
                onFinish: (page) => {
                    setShowPaymentMethodDefaultModal(false);
                },
            }
        );
    };

    return (
        <>
            <Head title={`Billing - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card
                    title="Subscriptions"
                    subtitle="View all your active subscriptions with SCDS."
                >
                    {props.subscriptions.length > 0 && (
                        <BasicList
                            items={props.subscriptions.map((item) => {
                                return {
                                    id: item.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex flex-col gap-y-3 text-sm"
                                                key={item.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        {item.name}.
                                                        {/*{item.status}*/}
                                                    </div>
                                                    <div className="text-gray-500">
                                                        Started{" "}
                                                        {fromUnixTime(
                                                            item.billing_cycle_anchor
                                                        ).toLocaleDateString()}
                                                        {item.current_period_start &&
                                                            item.current_period_end && (
                                                                <>
                                                                    , Current
                                                                    period{" "}
                                                                    {fromUnixTime(
                                                                        item.current_period_start
                                                                    ).toLocaleDateString()}{" "}
                                                                    -{" "}
                                                                    {fromUnixTime(
                                                                        item.current_period_end
                                                                    ).toLocaleDateString()}
                                                                </>
                                                            )}
                                                        .
                                                    </div>
                                                </div>
                                                <div className="">
                                                    <div className="text-sm text-gray-500">
                                                        This subscription is
                                                        billed in{" "}
                                                        {item.currency_name}.
                                                        {item.discount && (
                                                            <>
                                                                {" "}
                                                                A discount is
                                                                applied to this
                                                                subscription,
                                                                meaning your
                                                                billed total may
                                                                be less.
                                                            </>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <Table
                                                data={item.items}
                                                columns={[
                                                    {
                                                        headerName:
                                                            "Product Name",
                                                        field: "product_name",
                                                    },
                                                    {
                                                        headerName: "Quantity",
                                                        field: "quantity",
                                                    },
                                                    {
                                                        headerName:
                                                            "Unit Price",
                                                        field: "price.unit_amount_period",
                                                    },
                                                    {
                                                        headerName: "Total",
                                                        field: "price.amount_period",
                                                    },
                                                ]}
                                            />
                                        </>
                                    ),
                                };
                            })}
                        />
                    )}
                </Card>

                <Card
                    footer={
                        <ButtonLink
                            href={route(
                                "central.tenants.billing.add_payment_method",
                                props.id
                            )}
                        >
                            Add a payment method
                        </ButtonLink>
                    }
                    title="Payment methods"
                    subtitle="Manage your payment methods."
                >
                    <FlashAlert className="mb-4" bag="payment_method" />

                    {props.payment_method && (
                        <div>
                            <p className="text-sm">
                                You can change your default payment method at
                                any time.
                            </p>
                        </div>
                    )}

                    {props.payment_methods.length > 0 && (
                        <BasicList
                            items={props.payment_methods.map((item) => {
                                return {
                                    id: item.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                                                key={item.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        {item.description}{" "}
                                                        {item.default && (
                                                            <>(default)</>
                                                        )}
                                                    </div>
                                                    {item.info_line && (
                                                        <div className="text-gray-500">
                                                            {item.info_line}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="block">
                                                    {!item.default && (
                                                        <>
                                                            <Button
                                                                variant="secondary"
                                                                onClick={() => {
                                                                    setShowPaymentMethodDefaultModal(
                                                                        true
                                                                    );
                                                                    setPaymentMethodDefaultModalData(
                                                                        item
                                                                    );
                                                                }}
                                                            >
                                                                Make default
                                                            </Button>

                                                            <Button
                                                                variant="danger"
                                                                className="ml-3"
                                                                onClick={() => {
                                                                    setShowPaymentMethodDeleteModal(
                                                                        true
                                                                    );
                                                                    setPaymentMethodDeleteModalData(
                                                                        item
                                                                    );
                                                                }}
                                                            >
                                                                Delete
                                                            </Button>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />
                    )}

                    {!props.payment_methods.length && (
                        <div>
                            <p className="text-sm">
                                You have no default payment method set up.
                            </p>
                        </div>
                    )}
                </Card>

                <Modal
                    show={showPaymentMethodDeleteModal}
                    onClose={() => setShowPaymentMethodDeleteModal(false)}
                    variant="danger"
                    title="Delete payment method"
                    buttons={
                        <>
                            <Button
                                variant="danger"
                                onClick={deletePaymentMethod}
                            >
                                Confirm
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() =>
                                    setShowPaymentMethodDeleteModal(false)
                                }
                            >
                                Cancel
                            </Button>
                        </>
                    }
                >
                    {paymentMethodDeleteModalData && (
                        <p>
                            Are you sure you want to delete{" "}
                            {paymentMethodDeleteModalData.description}?
                        </p>
                    )}
                </Modal>

                <Modal
                    show={showPaymentMethodDefaultModal}
                    onClose={() => setShowPaymentMethodDefaultModal(false)}
                    variant="primary"
                    title="Change default payment method"
                    buttons={
                        <>
                            <Button
                                variant="primary"
                                onClick={setDefaultPaymentMethod}
                            >
                                Confirm
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() =>
                                    setShowPaymentMethodDefaultModal(false)
                                }
                            >
                                Cancel
                            </Button>
                        </>
                    }
                >
                    {paymentMethodDefaultModalData && (
                        <>
                            <p className="mb-3">
                                Are you sure you want to make{" "}
                                {paymentMethodDefaultModalData.description} your
                                default payment method?
                            </p>

                            <p>
                                We will automatically charge all future
                                subscription payments to this payment method.
                            </p>
                        </>
                    )}
                </Modal>

                <Card
                    footer={
                        <ButtonLink
                            href={route(
                                "central.tenants.billing.portal",
                                props.id
                            )}
                        >
                            View more
                        </ButtonLink>
                    }
                >
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Invoices
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            View your invoice history.
                        </p>
                    </div>

                    <FlashAlert className="mb-4" bag="invoices" />

                    {props.invoices.length > 0 && (
                        <BasicList
                            items={props.invoices.map((item) => {
                                return {
                                    id: item.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                                                key={item.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        {
                                                            item.money_formatted_total
                                                        }
                                                        ,{" "}
                                                        {fromUnixTime(
                                                            item.created
                                                        ).toLocaleDateString()}
                                                    </div>
                                                </div>
                                                <div className="">
                                                    <>
                                                        <ButtonLink
                                                            variant="secondary"
                                                            href={item.link}
                                                            external
                                                        >
                                                            View
                                                        </ButtonLink>

                                                        <ButtonLink
                                                            variant="secondary"
                                                            className="ml-3"
                                                            href={item.pdf_link}
                                                            external
                                                        >
                                                            Download
                                                        </ButtonLink>
                                                    </>
                                                </div>
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />
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
