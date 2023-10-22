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
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import Radio from "@/Components/Form/Radio";
import Fieldset from "@/Components/Form/Fieldset";

type Props = {
    ledgers: [];
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Create Custom Ledger" />

            <Form
                initialValues={{
                    name: "",
                    type: "",
                }}
                validationSchema={yup.object().shape({
                    name: yup
                        .string()
                        .required("A name is required for this ledger")
                        .max(100, "The name must not exceed 100 characters"),
                    type: yup
                        .string()
                        .required("A type is required for this ledger")
                        .oneOf(
                            [
                                "asset",
                                "liability",
                                "equity",
                                "income",
                                "expense",
                            ],
                            "Must be one of the supported types"
                        ),
                })}
                hideDefaultButtons
                hideErrors
                method="post"
                action={route("payments.ledgers.new")}
            >
                <Card footer={<SubmissionButtons />}>
                    <RenderServerErrors />

                    <TextInput name="name" label="Ledger name" />

                    <Fieldset legend="Ledger type">
                        <Radio name="type" value="asset" label="Asset" />
                        <Radio
                            name="type"
                            value="liability"
                            label="Liability"
                            help="We don't currently recommend creating any ledgers with this type, but you can if you wish"
                        />
                        <Radio
                            name="type"
                            value="equity"
                            label="Equity"
                            help="We don't currently recommend creating any ledgers with this type, but you can if you wish"
                        />
                        <Radio name="type" value="income" label="Income" />
                        <Radio name="type" value="expense" label="Expense" />
                    </Fieldset>
                </Card>
            </Form>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title="Create Custom Ledger"
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

export default Index;
