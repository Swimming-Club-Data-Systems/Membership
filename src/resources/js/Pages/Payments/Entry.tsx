import React, { useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainLayout from "@/Layouts/MainLayout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Button from "@/Components/Button";
import Combobox from "@/Components/Form/Combobox";
import TextInput from "@/Components/Form/TextInput";
import Radio from "@/Components/Form/Radio";
import BasicList from "@/Components/BasicList";
import { router } from "@inertiajs/react";

type User = {
    manual_payment_entry_id: number;
    user_id: number;
    name: string;
    email: string;
};

type Props = {
    id: number;
    users: User[];
    lines: [];
};

const deleteUser = async (entry, user) => {
    router.delete(
        route("payments.entries.delete_user", { entry: entry, user: user }),
        {
            only: ["users", "flash"],
            preserveScroll: true,
            preserveState: true,
        }
    );
};

const User = (item: User) => {
    return {
        id: item.user_id,
        content: (
            <>
                <div
                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                    key={item.user_id}
                >
                    <div className="">
                        <div className="text-gray-900">{item.name}</div>
                        <div className="text-gray-500">{item.email}</div>
                    </div>
                    <div className="block">
                        <>
                            <Button
                                variant="danger"
                                className="ml-3"
                                onClick={() => {
                                    deleteUser(
                                        item.manual_payment_entry_id,
                                        item.user_id
                                    );
                                }}
                            >
                                Delete
                            </Button>
                        </>
                    </div>
                </div>
            </>
        ),
    };
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Manual Payment Entry"
                subtitle="Create a manual payment entry"
            />

            <div className="grid gap-4">
                <Form
                    initialValues={{
                        user_select: null,
                    }}
                    validationSchema={yup.object().shape({
                        user_select: yup
                            .number()
                            .typeError("You must choose a user")
                            .required("You must choose a user"),
                    })}
                    hideDefaultButtons
                    submitTitle="Add user"
                    formName="manage_users"
                    action={route("payments.entries.add_user", {
                        entry: props.id,
                    })}
                    method="post"
                    inertiaOptions={{
                        preserveScroll: true,
                        preserveState: true,
                    }}
                    hideErrors
                >
                    <Card
                        title="Users"
                        subtitle="Choose users to create manual payment entries for."
                        footer={<SubmissionButtons />}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="manage_users" />

                        {props.users.length > 0 && (
                            <BasicList items={props.users.map(User)} />
                        )}

                        <Combobox
                            endpoint={route("users.combobox")}
                            name="user_select"
                            label="User"
                            help="Start typing to find a user"
                        />
                    </Card>
                </Form>

                <Form
                    initialValues={{
                        description: "",
                        amount: 0,
                        type: "debit",
                        user_select: null,
                    }}
                    validationSchema={yup.object().shape({
                        description: yup
                            .string()
                            .required("A description is required")
                            .max(
                                255,
                                "Description must be 255 characters or less"
                            ),
                        amount: yup
                            .number()
                            .typeError("You must enter an amount")
                            .required("You must enter an amount")
                            .min(
                                0,
                                "You must enter an amount greater than £0.00"
                            )
                            .max(
                                1000,
                                "You must enter an amount less than or equal to £1000.00"
                            ),
                        type: yup
                            .string()
                            .oneOf(
                                ["credit", "debit"],
                                "The payment type must be one of Credit or Debit"
                            ),
                        journal_select: yup
                            .number()
                            .typeError("You must select a Journal Account")
                            .required("You must select a Journal Account"),
                    })}
                    hideDefaultButtons
                    submitTitle="Add line"
                    formName="manage_lines"
                    action={route("payments.entries.add_line", {
                        entry: props.id,
                    })}
                    method="post"
                    inertiaOptions={{
                        preserveScroll: true,
                        preserveState: true,
                    }}
                    hideErrors
                >
                    <Card
                        title="Line Items"
                        subtitle="Add multiple line items to this manual payment entry."
                        footer={<SubmissionButtons />}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="manage_lines" />

                        {props.users.length > 0 && (
                            <BasicList items={props.users.map(User)} />
                        )}

                        <TextInput
                            name="description"
                            label="Line description"
                        />

                        <TextInput name="amount" label="Line amount (£ GBP)" />

                        <div>
                            <Radio name="type" value="debit" label="Debit" />
                            <Radio name="type" value="credit" label="Credit" />
                        </div>

                        <Combobox
                            endpoint={route(
                                "payments.ledgers.journals.combobox"
                            )}
                            name="journal_select"
                            label="Journal account"
                            help="Start typing to find a journal"
                        />
                    </Card>
                </Form>
            </div>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        breadcrumbs={[
            { name: "Payments", route: "my_account.index" },
            { name: "Manual Payment Entry", route: "payments.entries.new" },
        ]}
    >
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Index;
