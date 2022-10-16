import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Layout from "./Layout";
import Form, {
    SubmissionButtons,
    RenderServerErrors,
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import "yup-phone";
import Card from "@/Components/Card";
import Select from "@/Components/Form/Select";
import { Inertia } from "@inertiajs/inertia";
import FlashAlert from "@/Components/FlashAlert";

const Show = (props) => {
    return (
        <>
            <Head title="My Account" />

            {/* <Container noMargin className="py-12"></Container> */}

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                {/* <form action="#" method="POST"> */}
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
                            .email("You must use a valid email address"),
                    })}
                    action={route("my_account.profile")}
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
                                Personal Information
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Tell us about yourself
                            </p>
                        </div>

                        <RenderServerErrors />
                        <FlashAlert className="mb-4" />

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

                            <div className="col-span-6 sm:col-span-4">
                                <TextInput
                                    name="email"
                                    type="email"
                                    label="Email address"
                                    help="If you change your email address, we'll send an email to verify it before the change takes effect."
                                    autoComplete="email"
                                />
                            </div>
                        </div>
                    </Card>
                </Form>
                {/* </form> */}
            </div>
        </>
    );
};

Show.layout = (page) => (
    <CentralMainLayout
        title="My Account"
        subtitle="Manage your personal details"
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Show;
