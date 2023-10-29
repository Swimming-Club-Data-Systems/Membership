import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import {
    PaymentMethodDetails,
    PaymentMethodDetailsProps,
} from "@/Pages/Payments/PaymentMethods";

const Index: Layout<PaymentMethodDetailsProps> = (
    props: PaymentMethodDetailsProps
) => {
    return (
        <>
            <Head
                title="Payment Methods"
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
                        name: "Payment Methods",
                        route: "users.payment_methods.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
                subtitle={`Manage ${props.user.name}'s payment cards and direct debit mandates`}
            />

            <PaymentMethodDetails {...props} />
        </>
    );
};

Index.layout = (page) => (
    <MainLayout>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Index;
