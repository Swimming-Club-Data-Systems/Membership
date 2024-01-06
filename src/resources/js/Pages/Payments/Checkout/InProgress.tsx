import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";

type Props = {
    id: number;
};

const Checkout: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={`Payment In Progress`}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    // { name: "Checkout", route: "payments.statements.index" },
                    // {
                    //     name: `#${props.id}`,
                    //     route: "payments.statements.show",
                    //     routeParams: props.id,
                    // },
                ]}
            />

            <MainHeader
                title={`Payment In Progress`}
                subtitle="Your payment has been started"
            ></MainHeader>
        </>
    );
};

Checkout.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Checkout;
