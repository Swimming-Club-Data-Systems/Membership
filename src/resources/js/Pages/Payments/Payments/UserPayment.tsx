import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import { Props } from "./Payment";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { PaymentContent } from "@/Pages/Payments/Payments/Payment";

const UserPayment: Layout<Props> = (props: Props) => {
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
                    {
                        name: `#${props.id}`,
                        route: "users.payments.show",
                        routeParams: {
                            user: props.user.id,
                            payment: props.id,
                        },
                    },
                ]}
                subtitle={`View payments for ${props.user.name}`}
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

UserPayment.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default UserPayment;
