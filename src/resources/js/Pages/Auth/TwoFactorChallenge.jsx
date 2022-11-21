import React from "react";
import Button from "@/Components/Button";
import AuthServices from "@/Layouts/AuthServices";
import { Head, useForm } from "@inertiajs/inertia-react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";

const TwoFactorChallenge = (props) => {
    const { post, processing } = useForm();

    const submit = (e) => {
        e.preventDefault();

        post(route("two_factor.resend"));
    };

    const onSubmit = (values, formikBag) => {
        Inertia.post(route("two_factor"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <AuthServices title="Two-factor authentication">
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
                <Button variant="secondary" disabled={processing} type="submit">
                    Resend code
                </Button>
            </form>
        </AuthServices>
    );
};

export default TwoFactorChallenge;
