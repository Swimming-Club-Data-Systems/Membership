import React from "react";
import * as yup from "yup";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Card from "@/Components/Card";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
    UnknownError,
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import Radio from "@/Components/Form/Radio";
import Fieldset from "@/Components/Form/Fieldset";

type Props = {
    ledger_id: number;
    ledger_name: string;
};

const New: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Create Journal"
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    { name: "Ledgers", route: "payments.ledgers.index" },
                    {
                        name: props.ledger_name,
                        route: "payments.ledgers.show",
                        routeParams: props.ledger_id,
                    },
                    {
                        name: "New Journal",
                        route: "payments.ledgers.journals.new",
                        routeParams: props.ledger_id,
                    },
                ]}
            />

            <Form
                initialValues={{
                    name: "",
                    currency: "GBP",
                }}
                validationSchema={yup.object().shape({
                    name: yup
                        .string()
                        .required("A name is required for this journal")
                        .max(100, "The name must not exceed 100 characters"),
                    currency: yup
                        .string()
                        .required("A currency is required for the journal")
                        .equals(["GBP"], "The only supported currency is GBP"),
                })}
                hideDefaultButtons
                hideErrors
                method="post"
                action={route("payments.ledgers.journals.new", props.ledger_id)}
            >
                <Card footer={<SubmissionButtons />}>
                    <RenderServerErrors />

                    <TextInput name="name" label="Ledger name" />

                    <TextInput
                        name="currency"
                        label="Currency ISO code"
                        disabled
                    />
                </Card>
            </Form>
        </>
    );
};

New.layout = (page) => (
    <MainLayout title="Create Journal" subtitle="Create a new custom ledgers">
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default New;
