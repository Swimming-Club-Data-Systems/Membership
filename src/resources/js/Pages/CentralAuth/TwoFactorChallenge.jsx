import React from "react";
import Button from "@/Components/Button";
import CentralAuthServices from "@/Layouts/CentralAuthServices";
import { Head, useForm, router } from "@inertiajs/react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";

const TwoFactorChallenge = (props) => {
    const { post, processing } = useForm();

    const submit = (e) => {
        e.preventDefault();

        post(route("two_factor.resend"));
    };

    const onSubmit = (values, formikBag) => {
        router.post(route("central.two_factor"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <CentralAuthServices title="Two-factor authentication">
            <Head title="Two-factor authentication" />

            <div className="mb-4 text-sm text-gray-600">
                To help keep your account secure, we'd like to make sure it's
                really you trying to sign in.
            </div>

            <Form
                initialValues={{
                    code: "",
                }}
                validationSchema={yup.object().shape({
                    code: yup
                        .string()
                        .length(6, "Authentication codes are 6 digits")
                        .required("You must enter an authentication code")
                        .matches(
                            /[0-9]{6,6}/,
                            "Authentication codes are 6 digits"
                        ),
                })}
                onSubmit={onSubmit}
                submitTitle="Verify"
                submitClass="w-full"
                hideClear
            >
                <TextInput
                    name="code"
                    type="text"
                    label="Authentication code"
                    autoComplete="one-time-code"
                    pattern="[0-9]*"
                    inputMode="numeric"
                />
            </Form>

            <div className="mb-4 mt-4 text-sm text-gray-600">
                {props.isTOTP && (
                    <>
                        Open the two-factor authenticator (TOTP) app on your
                        mobile device to view your authentication code.
                    </>
                )}

                {!props.isTOTP && (
                    <>
                        We've sent your two-factor authentication code to your
                        inbox.
                    </>
                )}
            </div>

            <form onSubmit={submit}>
                <Button variant="secondary" disabled={processing}>
                    Resend code
                </Button>
            </form>
        </CentralAuthServices>
    );
};

export default TwoFactorChallenge;
