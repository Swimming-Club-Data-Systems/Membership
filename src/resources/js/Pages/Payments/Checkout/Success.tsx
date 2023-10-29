import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import Card from "@/Components/Card";
import { PaymentLineItemsSummary } from "@/Components/Payments/Checkout/PaymentLineItemsSummary";
import ButtonLink from "@/Components/ButtonLink";

type Props = {
    auth: {
        user?: {
            name: string;
        };
    };
    id: number;
    payment_method: {
        id: number;
        description: string;
        info_line: string;
    };
    lines: {
        id: number;
        description: string;
        quantity: number;
        formatted_amount: string;
    }[];
    return_link?: string;
    return_link_text?: string;
    cancel_link?: string;
    cancel_link_text?: string;
    formatted_total: string;
    statement_descriptor: string;
};

const Success: Layout<Props> = (props: Props) => {
    const routes = props.auth?.user
        ? [{ name: "Payments", route: "payments.index" }]
        : [];

    return (
        <>
            <Head title={`Payment Successful`} breadcrumbs={routes} />

            <Container>
                <MainHeader
                    title={`Payment Successful`}
                    subtitle="Your payment has been successful"
                ></MainHeader>

                <div className="prose prose-sm mb-4">
                    <p>You paid {props.formatted_total}.</p>

                    <p>
                        We&apos;re sending a payment receipt to you as
                        confirmation of your payment.
                    </p>

                    {props.statement_descriptor && (
                        <p>
                            This transaction will appear on your statement as{" "}
                            {props.statement_descriptor},{" "}
                            {props.formatted_total}.
                        </p>
                    )}

                    {props.return_link && (
                        <p>
                            <ButtonLink href={props.return_link}>
                                {props.return_link_text || "Return"}
                            </ButtonLink>
                        </p>
                    )}

                    {!props.return_link && (
                        <p>
                            <ButtonLink href={route("index")}>
                                Back to home
                            </ButtonLink>
                        </p>
                    )}
                </div>

                <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight mb-4">
                    Payment Method
                </h2>
            </Container>

            <Container noMargin>
                <Card className="mb-4">
                    <div className="text-sm">
                        <div className="text-gray-900">
                            {props.payment_method.description}
                        </div>
                        {props.payment_method.info_line && (
                            <div className="text-gray-500">
                                {props.payment_method.info_line}
                            </div>
                        )}
                    </div>
                </Card>
            </Container>

            <Container>
                <h2 className="lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl sm:tracking-tight mb-4">
                    Payment Summary
                </h2>
            </Container>

            <Container noMargin>
                <div className="mb-4">
                    <PaymentLineItemsSummary data={props.lines} />
                </div>
            </Container>

            <Container>
                <p className="text-sm text-gray-900">
                    Payments and refunds are subject to the published terms and
                    conditions.
                </p>
            </Container>
        </>
    );
};

Success.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Success;
