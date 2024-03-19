import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import { formatDateTime } from "@/Utils/date-utils";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";

type PaymentItemContentProps = {
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
};

export type PaymentIndexProps = {
    user: {
        id: number;
        name: string;
    };
    payments: LaravelPaginatorProps<PaymentItemContentProps>;
};

export const PaymentItemContent: React.FC<PaymentItemContentProps> = (
    props,
) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.formatted_amount}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {formatDateTime(props.created_at)}
                        </div>
                    </div>
                </div>
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink text-sm text-gray-700 group-hover:text-gray-800 text-right">
                        {props.payment_method.description && (
                            <div>{props.payment_method.description}</div>
                        )}
                        {props.payment_method.information_line && (
                            <div>{props.payment_method.information_line}</div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
};

const Index: Layout<PaymentIndexProps> = (props: PaymentIndexProps) => {
    return (
        <>
            <Head
                title="Payments"
                breadcrumbs={[
                    { name: "Payments", route: "payments.index" },
                    {
                        name: "Card and Direct Debit Payments",
                        route: "payments.payments.index",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader title="Payments" subtitle="Payments"></MainHeader>

                <Collection
                    {...props.payments}
                    route="payments.payments.show"
                    itemRenderer={PaymentItemContent}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
