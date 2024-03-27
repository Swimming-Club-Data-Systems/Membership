import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import { formatDateTime } from "@/Utils/date-utils";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";

type BalanceTopUpContentProps = {
    id: number;
    formatted_amount: string;
    amount: number;
    scheduled_for: string;
    created_at: string;
    updated_at: string;
    payment_method?: {
        id: number;
        description: string;
        information_line: string;
        type: string;
    };
};

export type BalanceTopUpIndexProps = {
    user: {
        id: number;
        name: string;
    };
    balance_top_ups: LaravelPaginatorProps<BalanceTopUpContentProps>;
};

export const BalanceTopUpContent: React.FC<BalanceTopUpContentProps> = (
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
                            {props.scheduled_for && (
                                <>
                                    Scheduled for{" "}
                                    {formatDateTime(props.scheduled_for)},{" "}
                                </>
                            )}
                            Created at {formatDateTime(props.created_at)}
                        </div>
                    </div>
                </div>
                {props.payment_method && (
                    <div className="flex items-center min-w-0">
                        <div className="min-w-0 truncate overflow-ellipsis flex-shrink text-sm text-gray-700 group-hover:text-gray-800 text-right">
                            {props.payment_method.description && (
                                <div>
                                    Planned for{" "}
                                    {props.payment_method.description}
                                </div>
                            )}
                            {props.payment_method.information_line && (
                                <div>
                                    {props.payment_method.information_line}
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
};

const Index: Layout<BalanceTopUpIndexProps> = (
    props: BalanceTopUpIndexProps,
) => {
    return (
        <>
            <Head
                title="Balance Top Ups"
                breadcrumbs={[
                    { name: "Payments", route: "payments.index" },
                    {
                        name: "Balance Top Ups",
                        route: "payments.top_ups.index",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Balance Top Ups"
                    subtitle="Your current and previous balance top ups"
                ></MainHeader>

                <Collection
                    {...props.balance_top_ups}
                    route="payments.top_up.show"
                    itemRenderer={(item) => <BalanceTopUpContent {...item} />}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
