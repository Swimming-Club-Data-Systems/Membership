import React, { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
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
import { useRegistration } from "@web-auth/webauthn-helper";
import Alert from "@/Components/Alert";
import Checkbox from "@/Components/Form/Checkbox";
import { ShieldCheckIcon } from "@heroicons/react/outline";
import A from "@/Components/A";
import axios from "@/Utils/axios";

const Password = (props) => {
    const [deleteModalData, setDeleteModalData] = useState(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showTotpModal, setShowTotpModal] = useState(false);
    const [totpModalData, setTotpModalData] = useState(null);
    const [error, setError] = useState(null);

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

    const register = useRegistration(
        {
            actionUrl: route("my_account.webauthn_verify"),
            actionHeader: {
                "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
            },
            optionsUrl: route("my_account.webauthn_challenge"),
        },
        {
            "X-XSRF-TOKEN": getCookie("XSRF-TOKEN"),
        }
    );

    const handleRegister = async (ev, formikBag) => {
        try {
            await register({
                passkey_name: ev.name,
            });
            formikBag.resetForm();
            setError(null);
            // await getAuthenticators();
            Inertia.reload({
                only: ["passkeys", "flash"],
                preserveScroll: true,
            });
        } catch (error) {
            setError(error.message);
        }
    };

    const handleTotpSave = async (values, formikBag) => {
        try {
            Inertia.post(route("my_account.save_totp"), values, {
                onSuccess: (arg) => {
                    console.log(arg);
                    formikBag.resetForm();
                    setShowTotpModal(false);
                },
                preserveScroll: true,
            });

            // Inertia.reload({
            //     only: ["passkeys", "flash"],
            //     preserveScroll: true,
            // });
        } catch (error) {
            // setError(error.message);
        }
    };

    const deletePasskey = async () => {
        Inertia.delete(
            route("my_account.webauthn_delete", deleteModalData.id),
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    setShowDeleteModal(false);
                },
            }
        );
    };

    const loadAndShowTotp = async () => {
        let result = await axios.get(route("my_account.create_totp"));

        setTotpModalData(result.data);

        setShowTotpModal(true);
    };

    const closeTotp = () => {
        setShowTotpModal(false);
    };

    const hasTotp = props.has_totp;
    const totpButtons = (setup) => {
        if (setup) {
            return (
                <Button
                    type="button"
                    variant="danger"
                    onClick={() => setShowTotpModal(false)}
                >
                    Disable authenticator app
                </Button>
            );
        }
        return (
            <Button type="button" variant="primary" onClick={loadAndShowTotp}>
                Set up authenticator app
            </Button>
        );
    };

    // route('my_account.create_totp');

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
                    action={route("my_account.security")}
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
                                                        Created at{" "}
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

                <Card footer={totpButtons(hasTotp)}>
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Two-factor authenticator app
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            An additional layer of security for your account.
                        </p>
                    </div>

                    <Alert
                        variant="warning"
                        title="You may want to consider using a passkey instead of app based 2FA"
                    >
                        We&apos;re rolling out FIDO 2 Passkey support. Passkeys
                        are far more secure than traditional passwords and
                        can&apos;t be phished. They&apos;re also a form of
                        two-factor authentication as they require something you
                        have and something you either are (biometrics) or know
                        (your phone password).
                    </Alert>

                    <FlashAlert bag="totp" className="mb-4" />

                    {hasTotp && (
                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-4">
                                <Checkbox
                                    name="use_totp"
                                    label="Use an authenticator app"
                                />
                            </div>
                        </div>
                    )}

                    {!hasTotp && (
                        <>
                            <p className="text-sm text-gray-500 mb-4">
                                By default we send en email to your account
                                containing a verification code when you attempt
                                to log in. Setting up an authenticator app
                                achieves the same thing, but your app can
                                generate codes even when you have no connection.
                            </p>

                            <p className="text-sm text-gray-500">
                                This is called a Time-based One-Time Password.
                                It&apos;s an open standard supported by a range
                                of authenticator apps such as Microsoft
                                Authenticator and Google Authenticator. Safari
                                on Mac and iOS also has built in support, as do
                                a variety of password managers.
                            </p>
                        </>
                    )}
                </Card>

                <Modal
                    show={showTotpModal}
                    onClose={() => setShowTotpModal(false)}
                    // variant="danger"
                    Icon={ShieldCheckIcon}
                    title="Set up authenticator app"
                >
                    <Form
                        initialValues={{
                            code: "",
                        }}
                        validationSchema={yup.object().shape({
                            code: yup
                                .string()
                                .length(6, "Authentication codes are 6 digits")
                                .required(
                                    "You must enter an authentication code"
                                )
                                .matches(
                                    /[0-9]{6,6}/,
                                    "Authentication codes are 6 digits"
                                ),
                        })}
                        //action={route("my_account.save_totp")}
                        onSubmit={handleTotpSave}
                        submitTitle="Confirm code"
                        clearTitle="Cancel"
                        hideErrors
                        removeDefaultInputMargin
                        //method="post"
                        alwaysDirty
                        onClear={closeTotp}
                        alwaysClearable
                    >
                        {totpModalData && (
                            <div className="d-block">
                                <p className="mb-4">
                                    Scan the image below with the two-factor
                                    authentication app on your phone.
                                </p>
                                <p className="mb-4">
                                    If you&apos;re using your phone,{" "}
                                    <A href={totpModalData.url}>
                                        set up automatically
                                    </A>
                                    . If you can’t use a QR code, enter the text
                                    code below the QR code instead.
                                </p>

                                <img
                                    src={totpModalData.image}
                                    className="img-fluid mb-4 mx-auto sm:ml-0"
                                    srcSet={`${totpModalData.image2x} 2x, ${totpModalData.image3x} 3x`}
                                />

                                <p className="mb-4">{totpModalData.key}</p>

                                <FlashAlert bag="totp_modal" className="mb-4" />

                                <TextInput
                                    name="code"
                                    label="Enter the code from the application"
                                    help="After scanning the QR code image, the app will display a code that you can enter below."
                                />
                            </div>
                        )}
                    </Form>
                </Modal>
            </div>
        </>
    );
};

Password.layout = (page) => (
    <MainLayout title="My Account" subtitle="Manage your personal details">
        <Layout children={page} />
    </MainLayout>
);

export default Password;
