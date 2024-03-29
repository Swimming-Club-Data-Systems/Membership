import React, { useState } from "react";
import ButtonLink from "@/Components/ButtonLink.jsx";
import FlashAlert from "@/Components/FlashAlert.jsx";
import BasicList from "@/Components/BasicList.jsx";
import Button from "@/Components/Button.jsx";
import Card from "@/Components/Card.jsx";
import MainLayout from "@/Layouts/MainLayout.jsx";
import { Head, router } from "@inertiajs/react";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Modal from "@/Components/Modal";

export type PaymentMethodDetailsProps = {
    payment_methods: [];
    direct_debits: [];
    payment_method: object;
    is_admin?: boolean;
    user?: {
        id: number;
        name: string;
    };
};

export const PaymentMethodDetails: React.FC<PaymentMethodDetailsProps> = (
    props: PaymentMethodDetailsProps
) => {
    const [showPaymentMethodDeleteModal, setShowPaymentMethodDeleteModal] =
        useState(false);
    const [paymentMethodDeleteModalData, setPaymentMethodDeleteModalData] =
        useState(null);
    const [showPaymentMethodDefaultModal, setShowPaymentMethodDefaultModal] =
        useState(false);
    const [paymentMethodDefaultModalData, setPaymentMethodDefaultModalData] =
        useState(null);

    const PaymentMethod = (item) => {
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
                                {item.default && <>(default)</>}
                            </div>
                            {item.info_line && (
                                <div className="text-gray-500">
                                    {item.info_line}
                                </div>
                            )}
                        </div>
                        <div className="block">
                            <>
                                {item.type === "bacs_debit" && !item.default && (
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
                                )}

                                {!item.default && (
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
                                )}
                            </>
                        </div>
                    </div>
                </>
            ),
        };
    };

    const deletePaymentMethod = async () => {
        router.delete(
            route("payments.methods.delete", [paymentMethodDeleteModalData.id]),
            {
                only: ["payment_methods", "direct_debits", "flash"],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    setShowPaymentMethodDeleteModal(false);
                },
            }
        );
    };

    const setDefaultPaymentMethod = () => {
        router.put(
            route("payments.methods.update", [
                paymentMethodDefaultModalData.id,
            ]),
            {
                set_default: true,
            },
            {
                only: ["payment_methods", "direct_debits", "flash"],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    setShowPaymentMethodDefaultModal(false);
                },
            }
        );
    };

    return (
        <div className="grid gap-4">
            <Card
                footer={
                    props.is_admin ? null : (
                        <ButtonLink
                            href={route("payments.methods.new_direct_debit")}
                        >
                            Add a Direct Debit
                        </ButtonLink>
                    )
                }
                title="Direct Debit"
                subtitle="Manage your Direct Debit."
            >
                <FlashAlert className="mb-4" bag="direct_debit" />

                {props.payment_method && (
                    <div>
                        <p className="text-sm">
                            You can change your default Direct Debit at any
                            time. Direct Debit payments are covered by the
                            Direct Debit Guarantee.
                        </p>
                    </div>
                )}

                {props.direct_debits.length > 0 && (
                    <BasicList items={props.direct_debits.map(PaymentMethod)} />
                )}

                {!props.direct_debits.length && (
                    <div>
                        <p className="text-sm">
                            {props.is_admin
                                ? "No Direct Debit Instruction set up"
                                : "You have no Direct Debit Instruction set up."}
                        </p>
                    </div>
                )}
            </Card>

            <Card
                footer={
                    props.is_admin ? null : (
                        <ButtonLink href={route("payments.methods.new")}>
                            Add a payment method
                        </ButtonLink>
                    )
                }
                title="Card and other payment methods"
                subtitle="Manage credit and debit cards."
            >
                <FlashAlert className="mb-4" bag="payment_method" />

                {props.payment_method && !props.is_admin && (
                    <div>
                        <p className="text-sm">
                            You can change your default payment method at any
                            time.
                        </p>
                    </div>
                )}

                {props.payment_methods.length > 0 && (
                    <BasicList
                        items={props.payment_methods.map(PaymentMethod)}
                    />
                )}

                {!props.payment_methods.length && (
                    <div>
                        <p className="text-sm">
                            {props.is_admin
                                ? "No payment methods set up"
                                : "You have no payment methods set up."}
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
                        <Button variant="danger" onClick={deletePaymentMethod}>
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
                            We will automatically charge all future monthly
                            payments to this payment method.
                        </p>
                    </>
                )}
            </Modal>
        </div>
    );
};

const Index: Layout<PaymentMethodDetailsProps> = (
    props: PaymentMethodDetailsProps
) => {
    return (
        <>
            <Head title="Payment Methods" />

            <PaymentMethodDetails {...props} />
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title="Payment methods"
        subtitle="Manage your payment cards and direct debit mandates"
        breadcrumbs={[
            { name: "Payments", route: "my_account.index" },
            { name: "Payment Methods", route: "payments.methods.index" },
        ]}
    >
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Index;
