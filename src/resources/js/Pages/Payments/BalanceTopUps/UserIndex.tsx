import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Collection from "@/Components/Collection";
import { Layout } from "@/Common/Layout";
import {
    BalanceTopUpContent,
    BalanceTopUpIndexProps,
} from "@/Pages/Payments/BalanceTopUps/Index";

const Index: Layout<BalanceTopUpIndexProps> = (
    props: BalanceTopUpIndexProps
) => {
    return (
        <>
            <Head
                title="Balance Top Ups"
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
                        name: "Balance Top Ups",
                        route: "users.top_up.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
                subtitle={`View balance top ups for ${props.user.name}`}
            />

            <Container noMargin>
                <Collection
                    {...props.balance_top_ups}
                    route="users.top_up.show"
                    routeParams={[props.user.id]}
                    itemRenderer={BalanceTopUpContent}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout>{page}</MainLayout>;

export default Index;
