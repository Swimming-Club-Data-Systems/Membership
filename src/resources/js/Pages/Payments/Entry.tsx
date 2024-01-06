import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
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
import Stat from "@/Components/Stat";
import Stats from "@/Components/Stats";

type UserProps = {
    manual_payment_entry_id: number;
    user_id: number;
    name: string;
    email: string;
    posted: boolean;
};

type LineProps = {
    manual_payment_entry_id: number;
    line_id: number;
    description: string;
    credit: number;
    debit: number;
    credit_formatted: string;
    debit_formatted: string;
    type: string;
    journal_account_name: string;
    posted: boolean;
};

type Props = {
    id: number;
    users: UserProps[];
    lines: LineProps[];
    can_post: boolean;
    posted: boolean;
    debits: string;
    credits: string;
};

const deleteUser = async (entry, user) => {
    router.delete(
        route("payments.entries.delete_user", { entry: entry, user: user }),
        {
            only: ["users", "flash"],
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const deleteLine = async (entry, line) => {
    router.delete(
        route("payments.entries.delete_line", {
            entry: entry,
            line: line,
        }),
        {
            only: ["lines", "flash"],
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const User = (item: UserProps) => {
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
                    {!item.posted && (
                        <div className="block">
                            <>
                                <Button
                                    variant="danger"
                                    className="ml-3"
                                    onClick={() => {
                                        deleteUser(
                                            item.manual_payment_entry_id,
                                            item.user_id,
                                        );
                                    }}
                                >
                                    Delete
                                </Button>
                            </>
                        </div>
                    )}
                </div>
            </>
        ),
    };
};

const Line = (item: LineProps) => {
    return {
        id: item.line_id,
        content: (
            <>
                <div
                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                    key={item.line_id}
                >
                    <div className="">
                        <div className="text-gray-900">{item.description}</div>
                        <div className="text-gray-500">
                            {item.type === "debit" && (
                                <>£{item.debit_formatted} (debit)</>
                            )}
                            {item.type === "credit" && (
                                <>£{item.credit_formatted} (credit)</>
                            )}{" "}
                            &middot; {item.journal_account_name}
                        </div>
                    </div>
                    {!item.posted && (
                        <div className="block">
                            <>
                                <Button
                                    variant="danger"
                                    className="ml-3"
                                    onClick={() => {
                                        deleteLine(
                                            item.manual_payment_entry_id,
                                            item.line_id,
                                        );
                                    }}
                                >
                                    Delete
                                </Button>
                            </>
                        </div>
                    )}
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
                subtitle={
                    props.posted
                        ? "View a historic manual payment entry"
                        : "Create a manual payment entry"
                }
                breadcrumbs={[
                    { name: "Payments", route: "payments.index" },
                    {
                        name: "Manual Payment Entry",
                        route: "payments.entries.new",
                    },
                    {
                        name: `#${props.id}`,
                        route: props.posted
                            ? "payments.entries.view"
                            : "payments.entries.amend",
                        routeParams: props.id,
                    },
                ]}
            />

            <div className="grid gap-4">
                <Stats title="Statistics">
                    <Stat name="Total credits" stat={props.credits} />
                    <Stat name="Total debits" stat={props.debits} />
                    <Stat name="Users" stat={props.users.length} />
                </Stats>

                {props.posted && (
                    <>
                        <Card title="Transactions have been posted">
                            <div className="prose prose-sm">
                                <p>
                                    This manual payment entry has been posted to
                                    journals. You can no longer make any
                                    adjustments to it.
                                </p>

                                <p>
                                    If you find you need to make a correction,
                                    you should make an additional Manual Payment
                                    Entry to correct the amount.
                                </p>
                            </div>
                        </Card>
                    </>
                )}

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
                        footer={!props.posted ? <SubmissionButtons /> : null}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="manage_users" />

                        {props.users.length > 0 && (
                            <BasicList items={props.users.map(User)} />
                        )}

                        {!props.posted && (
                            <Combobox
                                endpoint={route("users.combobox")}
                                name="user_select"
                                label="User"
                                help="Start typing to find a user"
                            />
                        )}
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
                                "Description must be 255 characters or less",
                            ),
                        amount: yup
                            .number()
                            .typeError("You must enter an amount")
                            .required("You must enter an amount")
                            .min(
                                0,
                                "You must enter an amount greater than £0.00",
                            )
                            .max(
                                1000,
                                "You must enter an amount less than or equal to £1000.00",
                            ),
                        type: yup
                            .string()
                            .oneOf(
                                ["credit", "debit"],
                                "The payment type must be one of Credit or Debit",
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
                        title="Line items"
                        subtitle="Add multiple line items to this manual payment entry."
                        footer={!props.posted ? <SubmissionButtons /> : null}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="manage_lines" />

                        {props.lines.length > 0 && (
                            <BasicList items={props.lines.map(Line)} />
                        )}

                        {!props.posted && (
                            <>
                                <TextInput
                                    name="description"
                                    label="Line description"
                                />

                                <TextInput
                                    name="amount"
                                    label="Line amount (£ GBP)"
                                />

                                <div>
                                    <Radio
                                        name="type"
                                        value="debit"
                                        label="Debit (add a charge to the user's account)"
                                    />
                                    <Radio
                                        name="type"
                                        value="credit"
                                        label="Credit (add a refund to the user's account)"
                                    />
                                </div>

                                <Combobox
                                    endpoint={route(
                                        "payments.ledgers.journals.combobox",
                                    )}
                                    name="journal_select"
                                    label="Journal account (payment category)"
                                    help="Start typing to find a journal"
                                />
                            </>
                        )}
                    </Card>
                </Form>

                {props.can_post && (
                    <Form
                        initialValues={{}}
                        validationSchema={yup.object().shape({})}
                        hideErrors
                        hideDefaultButtons
                        hideClear
                        submitTitle="Post"
                        action={route("payments.entries.post", props.id)}
                        method="put"
                        inertiaOptions={{
                            preserveScroll: true,
                            preserveState: true,
                        }}
                        formName="post_transactions"
                    >
                        <Card
                            title="Post transactions"
                            subtitle="Post transactions to journals."
                            footer={<SubmissionButtons />}
                        >
                            <RenderServerErrors />
                            <FlashAlert
                                className="mb-4"
                                bag="post_transactions"
                            />

                            <p className="text-sm">
                                Are you finished? Post transactions to user
                                journals and journal accounts to complete your
                                manual payment entry.
                            </p>
                        </Card>
                    </Form>
                )}
            </div>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Index;
