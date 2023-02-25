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
import Modal from "@/Components/Modal";
import Button from "@/Components/Button";
import Combobox from "@/Components/Form/Combobox";
import { BanknotesIcon, UserIcon } from "@heroicons/react/24/outline";
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
    const [showUserSelectModal, setShowUserSelectModal] = useState(false);
    const [showCreateLineModal, setShowCreateLineModal] = useState(false);

    return (
        <>
            <Head
                title="Manual Payment Entry"
                subtitle="Create a manual payment entry"
            />

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
                action={route("payments.entries.add_user", { entry: props.id })}
                method="post"
                inertiaOptions={{
                    preserveScroll: true,
                    preserveState: true,
                }}
                hideErrors
            >
                <div className="grid gap-4">
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
                            endpoint="/component-testing-user-search"
                            name="user_select"
                            label="User"
                            help="Start typing to find a user"
                        />
                    </Card>

                    <Card
                        title="Payment Lines"
                        subtitle="Create payment lines."
                        footer={
                            <Button
                                onClick={() => setShowCreateLineModal(true)}
                            >
                                Add line
                            </Button>
                        }
                    >
                        <FlashAlert className="mb-4" bag="direct_debit" />
                    </Card>
                </div>
            </Form>

            <Form
                initialValues={{}}
                validationSchema={yup.object().shape({})}
                hideDefaultButtons
            >
                <Modal
                    show={showUserSelectModal}
                    onClose={() => {
                        // Call formik clear
                        setShowUserSelectModal(false);
                    }}
                    variant="primary"
                    title="Select user"
                    buttons={<SubmissionButtons />}
                    Icon={UserIcon}
                >
                    <Combobox
                        endpoint="/component-testing-user-search"
                        name="user_select"
                        label="User"
                        help="Start typing to find a user"
                    />
                </Modal>
            </Form>

            <Form
                initialValues={{
                    line_description: "",
                    line_price: "",
                    type: "debit",
                }}
                validationSchema={yup.object().shape({
                    line_description: yup.string().required().max(255),
                    line_price: yup.number().required().min(0).max(1000),
                    type: yup.string().oneOf(["credit", "debit"]),
                })}
                hideDefaultButtons
            >
                <Modal
                    show={showCreateLineModal}
                    onClose={() => {
                        // Call formik clear
                        setShowCreateLineModal(false);
                    }}
                    variant="primary"
                    title="Create line"
                    buttons={<SubmissionButtons />}
                    Icon={BanknotesIcon}
                >
                    <div className="">
                        <TextInput
                            name="line_description"
                            label="Line description"
                        />

                        <TextInput name="line_price" label="Line price" />

                        <Radio name="type" value="debit" label="Debit" />
                        <Radio name="type" value="credit" label="Credit" />
                    </div>
                </Modal>
            </Form>
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
