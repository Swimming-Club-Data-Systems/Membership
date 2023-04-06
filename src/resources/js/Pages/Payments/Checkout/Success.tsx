import React, { useEffect, useState } from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import {
    Elements,
    PaymentElement,
    PaymentRequestButtonElement,
    useElements,
    useStripe,
} from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import Alert from "@/Components/Alert";
import Select from "@/Components/Form/Select";
import Card from "@/Components/Card";
import Table from "@/Components/Table";

type Props = {
    id: number;
    stripe_publishable_key: string;
    client_secret: string;
    stripe_account: string;
    country: string;
    currency: string;
    total: number;
    payment_methods: object[];
    return_url: string;
    formatted_total: string;
};

const Checkout: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={`Payment Successful`}
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
                title={`Payment Successful`}
                subtitle="Your payment has been successful"
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
