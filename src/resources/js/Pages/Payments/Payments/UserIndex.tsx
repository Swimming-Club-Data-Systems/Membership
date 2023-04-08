import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { formatDate } from "@/Utils/date-utils";
import MainHeader from "@/Layouts/Components/MainHeader";
import {
    TransactionIndexProps,
    TransactionItemContent,
} from "@/Pages/Payments/Transactions/Index";
import { Layout } from "@/Common/Layout";

const Index: Layout<TransactionIndexProps> = (props: TransactionIndexProps) => {
    return (
        <>
            <Head
                title="Transactions"
                breadcrumbs={[
                    { name: "Users", route: "users.index" },
                    {
                        name: props.user.name,
                        route: "users.show",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: "Transactions",
                        route: "users.transactions.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
                subtitle={`View transactions for ${props.user.name}`}
            />

            <Container noMargin>
                <PlainCollection
                    {...props.transactions}
                    itemRenderer={TransactionItemContent}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout>{page}</MainLayout>;

export default Index;
