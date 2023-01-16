import React from "react";
import * as yup from "yup";
import MainLayout from "@/Layouts/MainLayout.jsx";
import { Head } from "@inertiajs/react";
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
    ledgers: [];
};

const New: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Create Journal" />

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
                action={route("payments.ledgers.new")}
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
    <MainLayout
        title="Create Journal"
        subtitle="Create a new custom ledgers"
        breadcrumbs={[
            { name: "Payments", route: "my_account.index" },
            { name: "Ledgers", route: "payments.ledgers.index" },
            { name: "Create", route: "payments.ledgers.new" },
        ]}
    >
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default New;
