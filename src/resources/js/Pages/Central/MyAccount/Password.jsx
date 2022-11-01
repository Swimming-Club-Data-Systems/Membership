import React, { useEffect, useState } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "./Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import TextInput from "@/Components/Form/TextInput";
import BasicList from "@/Components/BasicList";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import { Inertia } from "@inertiajs/inertia";
import useRegistration from "@/Pages/Auth/Helpers/useRegistration";
import Alert from "@/Components/Alert";
import {
    startRegistration,
    browserSupportsWebAuthn,
} from "@simplewebauthn/browser";
import axios from "@/Utils/axios";

const Password = (props) => {
    const [deleteModalData, setDeleteModalData] = useState(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [error, setError] = useState(null);
    const [canUsePlatformAuthenticator, setCanUsePlatformAuthenticator] =
        useState(false);

    useEffect(() => {
        (async () => {
            if (await browserSupportsWebAuthn()) {
                setCanUsePlatformAuthenticator(true);
            }
        })();
    }, []);

    const getCookie = (cName) => {
        const name = cName + "=";
        const cDecoded = decodeURIComponent(document.cookie); //to be careful
        const cArr = cDecoded.split("; ");
        let res;
        cArr.forEach((val) => {
            if (val.indexOf(name) === 0) res = val.substring(name.length);
        });
        return res;
    };

    const handleRegister = async (ev, formikBag) => {
        let asseResp;

        const request = await axios.post(
            route("central.my_account.webauthn_challenge"),
            {
                passkey_name: ev.name,
            }
        );

        const options = request.data;
        if (!options.excludeCredentials) {
            options.excludeCredentials = [];
        }

        try {
            // Pass the options to the authenticator and wait for a response
            asseResp = await startRegistration(options);
        } catch (error) {
            // Some basic error handling
            formikBag.setSubmitting(false);
            setError(error.message);
            return;
        }

        // POST the response to the endpoint that calls
        // @simplewebauthn/server -> verifyAuthenticationResponse()
        const verificationResponse = await axios.post(
            route("central.my_account.webauthn_verify"),
            asseResp
        );

        if (verificationResponse.data.success) {
            formikBag.resetForm();
            setError(null);
            Inertia.reload({
                only: ["passkeys", "flash"],
                preserveScroll: true,
            });
        } else {
            setError("Invalid registration");
        }
        formikBag.setSubmitting(false);
    };

    const deletePasskey = async () => {
        Inertia.delete(
            route("central.my_account.webauthn_delete", deleteModalData.id),
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

            {/*<Container noMargin className="py-12"></Container>*/}

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                {/* <form action="#" method="POST"> */}
                <Form
                    initialValues={{
                        password: "",
                        password_confirmation: "",
                    }}
                    validationSchema={yup.object().shape({
                        password: yup
                            .string()
                            .required("Please enter a new password"),
                        password_confirmation: yup
                            .string()
                            .required("Please confirm your new password")
                            .oneOf(
                                [yup.ref("password"), null],
                                "Passwords must match"
                            ),
                    })}
                    action={route("central.my_account.security")}
                    submitTitle="Change password"
                    hideClear
                    hideDefaultButtons
                    hideErrors
                    removeDefaultInputMargin
                    method="put"
                >
                    <Card footer={<SubmissionButtons />}>
                        <div>
                            <h3 className="text-lg leading-6 font-medium text-gray-900">
                                Password
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Change your account password.
                            </p>
                        </div>

                        <RenderServerErrors />
                        <FlashAlert className="mb-4" />

                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-4">
                                <TextInput
                                    name="password"
                                    type="password"
                                    label="New password"
                                    autoComplete="new-password"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                                <TextInput
                                    name="password_confirmation"
                                    type="password"
                                    label="Confirm new password"
                                    autoComplete="new-password"
                                />
                            </div>
                        </div>
                    </Card>
                </Form>

                <Card>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Passkeys
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Passwordless login for your club account.
                        </p>
                    </div>

                    <FlashAlert className="mb-4" bag="delete_credentials" />

                    {props.passkeys.length > 0 && (
                        <BasicList
                            items={props.passkeys.map((item) => {
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
                                                        {new Date(
                                                            item.created_at
                                                        ).toLocaleString()}
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
                            })}
                        />
                    )}

                    <Modal
                        show={showDeleteModal}
                        onClose={() => setShowDeleteModal(false)}
                        variant="danger"
                        title="Delete passkey"
                        buttons={
                            <>
                                <Button
                                    variant="danger"
                                    onClick={deletePasskey}
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

                    {/*<RenderServerErrors />*/}
                    {/*<FlashAlert className="mb-4" />*/}
                    {error && (
                        <Alert variant="error" title="Error">
                            {error}
                        </Alert>
                    )}

                    <Form
                        initialValues={{
                            name: "",
                        }}
                        validationSchema={yup.object().shape({
                            name: yup
                                .string()
                                .required(
                                    "A name is required for your passkey"
                                ),
                        })}
                        // action={route("my_account.additional_email")}
                        submitTitle="Add passkey"
                        hideClear
                        // hideDefaultButtons
                        hideErrors
                        removeDefaultInputMargin
                        formName="manage_passkeys"
                        onSubmit={handleRegister}
                    >
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" bag="manage_passkeys" />

                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-3">
                                <TextInput
                                    name="name"
                                    label="Passkey name"
                                    help="Name your passkey to help you identify the device or keychain it is stored in."
                                />
                            </div>
                        </div>
                    </Form>

                    <div className="grid grid-cols-6 gap-6"></div>
                </Card>
            </div>
        </>
    );
};

Password.layout = (page) => (
    <CentralMainLayout
        title="My Account"
        subtitle="Manage your personal details"
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Password;
