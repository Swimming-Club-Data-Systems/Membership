import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { formatDateTime } from "@/Utils/date-utils";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";

type TransactionItemContentProps = {
    id: number;
    memo: string;
    debit: number;
    credit: number;
    debit_formatted: string;
    credit_formatted: string;
    currency: string;
    posted_at: string;
};

export type TransactionIndexProps = {
    user: {
        id: number;
        name: string;
    };
    transactions: TransactionItemContentProps[];
};

export const TransactionItemContent: React.FC<TransactionItemContentProps> = (
    props
) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium group-hover:text-indigo-700">
                            {props.memo}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {formatDateTime(props.posted_at)}
                        </div>
                    </div>
                </div>
                <div className="ml-2 flex flex-shrink-0 text-sm">
                    {props.debit && <>{props.debit_formatted}</>}
                    {props.credit && <>{props.credit_formatted}</>}
                </div>
            </div>
        </>
    );
};

const Index: Layout<TransactionIndexProps> = (props) => {
    return (
        <>
            <Head
                title="Transactions"
                breadcrumbs={[
                    { name: "Payments", route: "payments.index" },
                    {
                        name: "Transactions",
                        route: "payments.transactions.index",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Transactions"
                    subtitle="Transactions"
                ></MainHeader>

                <PlainCollection
                    {...props.transactions}
                    itemRenderer={TransactionItemContent}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
