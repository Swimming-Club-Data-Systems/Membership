import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Collection from "@/Components/Collection";
import { Layout } from "@/Common/Layout";
import {
    PaymentItemContent,
    PaymentIndexProps,
} from "@/Pages/Payments/Payments/Index";

const Index: Layout<PaymentIndexProps> = (props: PaymentIndexProps) => {
    return (
        <>
            <Head
                title="Payments"
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
                        name: "Payments",
                        route: "users.payments.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
                subtitle={`View payments for ${props.user.name}`}
            />

            <Container noMargin>
                <Collection
                    {...props.payments}
                    route="users.payments.show"
                    routeParams={[props.user.id]}
                    itemRenderer={(item) => <PaymentItemContent {...item} />}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout>{page}</MainLayout>;

export default Index;
