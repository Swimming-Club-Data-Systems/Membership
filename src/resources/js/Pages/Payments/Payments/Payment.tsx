import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import { formatDateTime } from "@/Utils/date-utils";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Card from "@/Components/Card";
import { DefinitionList } from "@/Components/DefinitionList";
import BasicList from "@/Components/BasicList";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import FlashAlert from "@/Components/FlashAlert";
import Table from "@/Components/Table";
import BigNumber from "bignumber.js";
import ArrayErrorMessage from "@/Components/Form/ArrayErrorMessage";
import DecimalInput from "@/Components/Form/DecimalInput";

type LineRefund = {
    id: number;
    amount: number;
    formatted_amount: string;
    line_refund_amount: number;
    formatted_line_refund_amount: string;
    line_refund_description: string;
    created_at: string;
};

type LineRefundProps = {
    refunds: LineRefund[];
};

export type Props = {
    id: number;
    is_administrator: boolean;
    formatted_amount: string;
    formatted_amount_refunded: string;
    payment_method: {
        id: number;
        description: string;
        information_line: string;
        type: string;
    };
    stripe_id: string;
    stripe_status: string;
    amount: number;
    amount_refunded: number;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
    };
    line_items: {
        id: number;
        description: string;
        formatted_amount_total: string;
        formatted_amount_discount: string;
        formatted_amount_refunded: string;
        formatted_amount_subtotal: string;
        formatted_amount_tax: string;
        formatted_unit_amount: string;
        quantity: number;
        amount_refundable: string;
        amount_refundable_int: number;
        amount_refunded_int: number;
        refunds: LineRefund[];
    }[];
    refunds: {
        id: number;
        formatted_amount: string;
        amount: number;
        created_at: string;
        refunder: {
            id: number;
            name: string;
        };
    }[];
};

const PaymentLineRefunds: React.FC<LineRefundProps> = ({ refunds }) => {
    if (refunds.length > 0) {
        return (
            <>
                <hr />
                <Table
                    columns={[
                        {
                            headerName: "Date",
                            field: "created_at",
                            render: (value: string) => formatDateTime(value),
                        },
                        {
                            headerName: "Amount refunded",
                            field: "formatted_line_refund_amount",
                        },
                    ]}
                    data={refunds}
                />
            </>
        );
    }

    return <></>;
};

export const PaymentContent: React.FC<Props> = (props) => {
    const items = [
        {
            key: "date",
            term: "Date",
            definition: formatDateTime(props.created_at),
        },
        {
            key: "payment_method",
            term: "Paid with",
            definition: (
                <>
                    <p>{props.payment_method.description}</p>
                    <p>{props.payment_method.information_line}</p>
                </>
            ),
        },
    ];

    if (props.is_administrator) {
        items.push({
            key: "stripe_id",
            term: "Stripe Payment Intent ID",
            definition: props.stripe_id,
        });
    }

    items.push(
        {
            key: "amount",
            term: "Amount",
            definition: props.formatted_amount,
        },
        {
            key: "amount_refunded",
            term: "Amount refunded",
            definition: props.formatted_amount_refunded,
        }
    );

    return (
        <>
            <div className="grid gap-4">
                <Card title="Payment Details">
                    <DefinitionList items={items} verticalPadding={2} />
                </Card>

                <Form
                    validationSchema={yup.object().shape({
                        lines: yup
                            .array()
                            .of(
                                yup.object().shape({
                                    refund_amount: yup
                                        .number()
                                        .typeError(
                                            "The amount must be a number."
                                        )
                                        .min(
                                            0,
                                            "The amount to refund must be greater than zero."
                                        )
                                        .max(
                                            yup.ref("amount_refundable"),
                                            "The amount to refund must be less than or equal to £${max}."
                                        ),
                                })
                            )
                            .test(
                                "total-gt-zero",
                                "The total to refund must be greater than zero.",
                                (value) => {
                                    const sum = value.reduce(
                                        (accumulator, currentValue) => {
                                            if (currentValue.refund_amount) {
                                                try {
                                                    const value = new BigNumber(
                                                        currentValue.refund_amount
                                                    );
                                                    return accumulator.plus(
                                                        value
                                                    );
                                                } catch {
                                                    // If error return old value
                                                    return accumulator;
                                                }
                                            } else {
                                                return accumulator;
                                            }
                                        },
                                        new BigNumber("0")
                                    );
                                    return sum.gt(new BigNumber("0"));
                                }
                            ),
                        reason: yup
                            .string()
                            .oneOf(
                                [
                                    "n/a",
                                    "duplicate",
                                    "fraudulent",
                                    "requested_by_customer",
                                ],
                                "Please select a valid option"
                            ),
                    })}
                    submitTitle="Refund items"
                    method="post"
                    action={route("payments.payments.refund", props.id)}
                    hideDefaultButtons
                    confirm={{
                        type: "danger",
                        message: (
                            <>Are you sure you want to refund these payments?</>
                        ),
                        confirmText: "Refund items",
                    }}
                >
                    <Card
                        title="Line Items"
                        footer={props.is_administrator && <SubmissionButtons />}
                    >
                        <FlashAlert className="mb-4" />

                        <BasicList
                            items={props.line_items.map((item, idx) => {
                                return {
                                    id: item.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex align-middle justify-between text-sm"
                                                key={item.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        {item.description}
                                                    </div>
                                                    <div className="text-gray-500">
                                                        {item.quantity} &times;{" "}
                                                        {
                                                            item.formatted_unit_amount
                                                        }{" "}
                                                        (Total{" "}
                                                        {
                                                            item.formatted_amount_total
                                                        }
                                                        {item.amount_refunded_int >
                                                            0 && (
                                                            <>
                                                                ,{" "}
                                                                {
                                                                    item.formatted_amount_refunded
                                                                }{" "}
                                                                refunded
                                                            </>
                                                        )}
                                                        )
                                                    </div>
                                                    {item.refunds.length >
                                                        0 && (
                                                        <PaymentLineRefunds
                                                            refunds={
                                                                item.refunds
                                                            }
                                                        />
                                                    )}
                                                </div>
                                                {props.is_administrator &&
                                                    item.amount_refundable_int >
                                                        0 && (
                                                        <div className="w-full md:w-96">
                                                            <DecimalInput
                                                                name={`lines[${idx}].refund_amount`}
                                                                label="Refund amount"
                                                                precision={2}
                                                            />
                                                        </div>
                                                    )}
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />

                        <ArrayErrorMessage name="lines" />
                    </Card>
                </Form>

                {props.refunds.length > 0 && (
                    <Card title="Refunds">
                        <BasicList
                            items={props.refunds.map((item, idx) => {
                                return {
                                    id: item.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex align-middle justify-between text-sm"
                                                key={item.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        {item.formatted_amount}
                                                    </div>
                                                    <div className="text-gray-500">
                                                        Refunded by{" "}
                                                        {item.refunder.name} at{" "}
                                                        {formatDateTime(
                                                            item.created_at
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />
                    </Card>
                )}
            </div>
        </>
    );
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Transactions"
                breadcrumbs={[
                    { name: "Payments", route: "payments.index" },
                    {
                        name: "Card and Direct Debit Payments",
                        route: "payments.payments.index",
                    },
                    {
                        name: `#${props.id}`,
                        route: "payments.payments.show",
                        routeParams: props.id,
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={`Payment #${props.id}`}
                    subtitle={props.formatted_amount}
                ></MainHeader>

                <PaymentContent {...props} />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
