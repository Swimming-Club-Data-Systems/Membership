import React, { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head, router } from "@inertiajs/react";
import Layout from "./Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import "yup-phone";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Checkbox from "@/Components/Form/Checkbox";
import Fieldset from "@/Components/Form/Fieldset";
import BasicList from "@/Components/BasicList";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";

const Email = (props) => {
    const validationSchemaObject = {
        email: yup
            .string()
            .required("An email address is required")
            .email("You must use a valid email address"),
        email_comms: yup
            .bool()
            .oneOf([true, false], "Must either be true or false"),
        sms_comms: yup
            .bool()
            .oneOf([true, false], "Must either be true or false"),
    };
    const customCategoryValidation = {};
    props.notify_categories.forEach((category) => {
        customCategoryValidation[category.id] = yup
            .bool()
            .oneOf([true, false], "Must either be true or false");
    });
    if (props.notify_categories.length > 0) {
        validationSchemaObject.notify_categories = yup
            .object()
            .shape(customCategoryValidation);
    }

    const [deleteModalData, setDeleteModalData] = useState(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const deleteAdditionalRecipient = async () => {
        router.delete(
            route("notify_additional_emails.delete", deleteModalData.id),
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setShowDeleteModal(false);
                },
            }
        );
    };

    return (
        <>
            <Head title="My Account" />

            {/* <Container noMargin className="py-12"></Container> */}

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                {/* <form action="#" method="POST"> */}
                <Form
                    initialValues={{
                        email: "",
                        email_comms: false,
                        sms_comms: false,
                        notify_categories: {},
                    }}
                    validationSchema={yup
                        .object()
                        .shape(validationSchemaObject)}
                    action={route("my_account.email")}
                    submitTitle="Save"
                    hideClear
                    hideDefaultButtons
                    hideErrors
                    removeDefaultInputMargin
                    method="put"
                >
                    <Card footer={<SubmissionButtons />}>
                        <div>
                            <h3 className="text-lg leading-6 font-medium text-gray-900">
                                Notifications
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Manage your email options.
                            </p>
                        </div>

                        <RenderServerErrors />
                        <FlashAlert className="mb-4" />

                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-4">
                                <TextInput
                                    name="email"
                                    type="email"
                                    label="Email address"
                                    help="If you change your email address, we'll send an email to verify it before the change takes effect."
                                />
                            </div>

                            <div className="col-span-6">
                                <Fieldset legend="Email subscription options">
                                    <Checkbox
                                        name="email_comms"
                                        label="Receive squad updates by email"
                                        help="Squad updates include emails from your coaches. You'll still receive emails relating to your account if you don't receive updates."
                                    />

                                    {props.notify_categories.map((category) => {
                                        return (
                                            <Checkbox
                                                name={`notify_categories.${category.id}`}
                                                label={category.name}
                                                help={category.description}
                                                key={category.id}
                                            />
                                        );
                                    })}
                                </Fieldset>
                            </div>

                            <div className="col-span-6">
                                <Fieldset legend="SMS options">
                                    <Checkbox
                                        name="sms_comms"
                                        label="Receive urgent squad updates by SMS"
                                        help="Get SMS messages when the club needs to share news quickly. Some clubs may not use this feature."
                                    />
                                </Fieldset>
                            </div>
                        </div>
                    </Card>
                </Form>

                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Additional recipients
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Allow a partner or others get important squad update
                            emails
                        </p>
                    </div>

                    <FlashAlert
                        className="mb-4"
                        bag="delete_additional_emails"
                    />

                    {props.notify_additional_emails.length > 0 && (
                        <BasicList
                            items={props.notify_additional_emails.map(
                                (item) => {
                                    return {
                                        id: item.id,
                                        content: (
                                            <>
                                                <div
                                                    className="flex align-middle justify-between text-sm"
                                                    key={item.id}
                                                >
                                                    <div className="">
                                                        <div className="text-gray-900">
                                                            {item.name}
                                                        </div>
                                                        <div className="text-gray-500">
                                                            <a
                                                                href={`mailto:${item.email}`}
                                                            >
                                                                {item.email}
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div className="">
                                                        <Button
                                                            variant="danger"
                                                            onClick={() => {
                                                                setShowDeleteModal(
                                                                    true
                                                                );
                                                                setDeleteModalData(
                                                                    item
                                                                );
                                                            }}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </div>
                                            </>
                                        ),
                                    };
                                }
                            )}
                        />
                    )}

                    <Modal
                        show={showDeleteModal}
                        onClose={() => setShowDeleteModal(false)}
                        variant="danger"
                        title="Delete additional recipient"
                        buttons={
                            <>
                                <Button
                                    variant="danger"
                                    onClick={deleteAdditionalRecipient}
                                >
                                    Confirm
                                </Button>
                                <Button
                                    variant="secondary"
                                    onClick={() => setShowDeleteModal(false)}
                                >
                                    Cancel
                                </Button>
                            </>
                        }
                    >
                        {deleteModalData && (
                            <p>
                                Are you sure you want to delete{" "}
                                {deleteModalData.name}?
                            </p>
                        )}
                    </Modal>

                    <Form
                        initialValues={{
                            email: "",
                            name: "",
                        }}
                        validationSchema={yup.object().shape({
                            email: yup
                                .string()
                                .required("An email address is required")
                                .email("You must use a valid email address"),
                            name: yup.string().required("A name is required"),
                        })}
                        action={route("my_account.additional_email")}
                        submitTitle="Add new recipient"
                        hideClear
                        // hideDefaultButtons
                        hideErrors
                        removeDefaultInputMargin
                        formName="additional_email"
                        inertiaOptions={{
                            preserveScroll: true,
                        }}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="additional_email" />

                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-3">
                                <TextInput name="name" label="Name" />
                            </div>

                            <div className="col-span-6 sm:col-span-3">
                                <TextInput
                                    name="email"
                                    type="email"
                                    label="Email address"
                                />
                            </div>

                            <div className="col-span-6"></div>
                        </div>
                    </Form>
                </Card>
                {/* </form> */}
            </div>
        </>
    );
};

Email.layout = (page) => (
    <MainLayout title="My Account" subtitle="Manage your personal details">
        <Layout children={page} />
    </MainLayout>
);

export default Email;
