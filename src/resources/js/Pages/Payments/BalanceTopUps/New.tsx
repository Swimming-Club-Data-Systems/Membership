import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import NativeDateInput from "@/Components/Form/NativeDateInput";
import DecimalInput from "@/Components/Form/DecimalInput";
import formatISO from "date-fns/formatISO";
import { addBusinessDays } from "date-fns";
import TextInput from "@/Components/Form/TextInput";
import Alert from "@/Components/Alert";

export type Props = {
    user: {
        id: number;
        name: string;
    };
    initiator: {
        id: number;
        name: string;
    };
    payment_method?: {
        description: string;
    };
};

const New: Layout<Props> = (props: Props) => {
    const todaysDate = formatISO(Date.now(), {
        representation: "date",
    });
    const maxDate = addBusinessDays(Date.now(), 10);

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
                        name: "Balance Top Ups",
                        route: "users.top_up.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: "New",
                        route: "users.top_up.new",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Create Balance Top Up"}
                    subtitle={`Initiate a balance top up for ${props.user.name}`}
                ></MainHeader>

                <Form
                    validationSchema={yup.object().shape({
                        scheduled_for: yup
                            .date()
                            .required("A date is required.")
                            .typeError("A date is required.")
                            .min(
                                todaysDate,
                                "The balance top up must be scheduled for today or a later date."
                            )
                            .max(
                                maxDate,
                                "The balance top up must be scheduled for within 10 business days of today."
                            ),
                        amount: yup
                            .number()
                            .required("An amount is required.")
                            .typeError("The amount must be a number.")
                            .min(1, "The minimum balance top up amount is £1.")
                            .max(
                                1000,
                                "The maximum balance top up amount is £1,000."
                            ),
                    })}
                    initialValues={{
                        user: props.user.name,
                        initiator: props.initiator.name,
                        payment_method: props.payment_method?.description,
                        scheduled_for: todaysDate,
                        amount: "0",
                    }}
                    confirm={{
                        type: "primary",
                        message: (
                            <>
                                Are you sure you want to create this balance top
                                up?
                            </>
                        ),
                        confirmText: "Confirm",
                    }}
                    submitTitle="Create Balance Top Up"
                    action={route("users.top_up.create", props.user.id)}
                    method="post"
                    disabled={!props.payment_method}
                >
                    {!props.payment_method && (
                        <Alert
                            title="No payment methods"
                            variant="danger"
                            className="mb-3"
                        >
                            You can not create a Balance Top Up for{" "}
                            {props.user.name} as they don&apos;t have an active
                            Direct Debit set up.
                        </Alert>
                    )}

                    <TextInput name="user" label="User" readOnly />

                    <TextInput
                        name="initiator"
                        label="Balance top up initiator"
                        readOnly
                    />

                    <TextInput
                        name="payment_method"
                        label="Payment method"
                        readOnly
                    />

                    <NativeDateInput
                        name="scheduled_for"
                        label="Schedule for"
                        min={todaysDate}
                        max={formatISO(maxDate, {
                            representation: "date",
                        })}
                    />

                    <DecimalInput
                        name="amount"
                        label="Amount (£)"
                        precision={2}
                    />

                    <p className="text-sm mb-3">
                        Your username will be recorded as the initiator of this
                        Balance Top Up.
                    </p>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
