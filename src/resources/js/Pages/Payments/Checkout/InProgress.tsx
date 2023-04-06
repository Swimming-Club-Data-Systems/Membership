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
