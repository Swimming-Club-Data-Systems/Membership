import React, { ReactNode, useState } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import { UserAddIcon } from "@heroicons/react/outline";

type Props = {
    id: number;
    name: string;
};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const Index: Layout<Props> = (props: Props) => {
    const [showNewUserModal, setShowNewUserModal] = useState(false);

    return (
        <>
            <Head title={`Tenant Administrators - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card
                    title="Tenant Administrators"
                    subtitle="Choose who has access to SCDS System Administration."
                    footer={
                        <Button onClick={() => setShowNewUserModal(true)}>
                            Add administrator
                        </Button>
                    }
                >
                    <FlashAlert className="mb-4" />

                    <div>
                        <p className="text-sm">
                            While most settings for your club membership system
                            can be managed inside the application, billing and
                            your Stripe account can only be managed here in SCDS
                            System Administration.
                        </p>
                    </div>
                </Card>

                <Modal
                    show={showNewUserModal}
                    onClose={() => setShowNewUserModal(false)}
                    variant="primary"
                    title="Add a new administrator"
                    Icon={UserAddIcon}
                >
                    <p className="text-sm mb-3">
                        To add a new administrator, provide the following
                        details. We&apos;ll then send an email to the user
                        inviting them to continue setting up their account.
                    </p>

                    <Form
                        initialValues={{
                            first_name: "",
                            last_name: "",
                            email: "",
                        }}
                        validationSchema={yup.object().shape({
                            first_name: yup
                                .string()
                                .required("A first name is required"),
                            last_name: yup
                                .string()
                                .required("A last name is required"),
                            email: yup
                                .string()
                                .required("An email address is required")
                                .email("You must enter a valid email address"),
                        })}
                        removeDefaultInputMargin
                        clearTitle="Cancel"
                        submitTitle="Add user"
                        alwaysClearable
                        onClear={() => setShowNewUserModal(false)}
                    >
                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-3">
                                <TextInput
                                    name="first_name"
                                    label="First name"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-3">
                                <TextInput name="last_name" label="Last name" />
                            </div>

                            <div className="col-span-6">
                                <TextInput
                                    name="email"
                                    label="Email address"
                                    type="email"
                                />
                            </div>
                        </div>
                    </Form>
                </Modal>
            </div>
        </>
    );
};

Index.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout>{page}</Layout>
    </CentralMainLayout>
);

export default Index;
