import React, { useEffect } from "react";
import Button from "@/Components/Button";
import AuthServices from "@/Layouts/AuthServices";
import Input from "@/Components/Input";
import Label from "@/Components/Label";
import ValidationErrors from "@/Components/ValidationErrors";
import { Head, useForm } from "@inertiajs/inertia-react";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: "",
    });

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.value);
    };

    const submit = (e) => {
        e.preventDefault();

        post(route("password.confirm"));
    };

    const onSubmit = (values, formikBag) => {
        Inertia.post(route("password.confirm"), values, {
            onSuccess: (arg) => console.log(arg),
        });
    };

    return (
        <AuthServices title="Confirm your password">
            <Head title="Confirm Password" />

            <div className="mb-4 text-sm text-gray-600">
                This is a secure area of the application. Please confirm your
                password before continuing.
            </div>

            <Form
                initialValues={{
                    password: "",
                }}
                validationSchema={yup.object().shape({
                    password: yup.string().required("A password is required"),
                })}
                onSubmit={onSubmit}
                submitTitle="Confirm"
                hideClear
            >
                <TextInput
                    name="password"
                    type="password"
                    autoFocus
                    autoComplete="password"
                />
            </Form>

            {/* <ValidationErrors errors={errors} />

            <form onSubmit={submit}>
                <div className="mt-4">
                    <Label forInput="password" value="Password" />

                    <Input
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        isFocused={true}
                        handleChange={onHandleChange}
                    />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <Button className="ml-4" processing={processing}>
                        Confirm
                    </Button>
                </div>
            </form> */}
        </AuthServices>
    );
}
