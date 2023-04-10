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

type Props = {
    id: number;
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
    }[];
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
            term: "Payment method",
            definition: (
                <>
                    <p>{props.payment_method.description}</p>
                    <p>{props.payment_method.information_line}</p>
                </>
            ),
        },
        {
            key: "stripe_id",
            term: "Stripe Payment Intent ID",
            definition: props.stripe_id,
        },
        {
            key: "amount",
            term: "Amount",
            definition: props.formatted_amount,
        },
        {
            key: "amount_refunded",
            term: "Amount refunded",
            definition: props.formatted_amount_refunded,
        },
    ];

    // @ts-ignore
    // @ts-ignore
    // @ts-ignore
    return (
        <>
            <div className="grid gap-4">
                <Card title="Payment Details">
                    <DefinitionList items={items} verticalPadding={2} />
                </Card>

                <Form
                    validationSchema={yup.object().shape({
                        lines: yup.array().of(
                            yup.object().shape({
                                refund_amount: yup
                                    .number()
                                    .min(
                                        0,
                                        "The amount to refund must be greater than zero"
                                    )
                                    .max(
                                        yup.ref("amount_refundable"),
                                        "The amount to refund must be less than or equal to £${max}"
                                    ),
                            })
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
                >
                    <Card title="Line Items" footer={<SubmissionButtons />}>
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
                                                        )
                                                    </div>
                                                </div>
                                                <div>
                                                    <TextInput
                                                        name={`lines[${idx}].refund_amount`}
                                                        label="Refund amount"
                                                    />
                                                </div>
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />
                    </Card>
                </Form>
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
