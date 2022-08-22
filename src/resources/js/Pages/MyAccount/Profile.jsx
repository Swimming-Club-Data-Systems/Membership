import React from "react";
import MainLayout from "@/Layouts/MainLayout";
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
                        mobile: "",
                        address_line_1: "",
                        address_line_2: "",
                        city: "",
                        county: "",
                        post_code: "",
                        country: "",
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
                        mobile: yup
                            .string()
                            .phone(
                                undefined,
                                undefined,
                                "Please enter a valid phone number"
                            )
                            .required("A phone number is required"),
                        address_line_1: yup
                            .string()
                            .required(
                                "A street and house name or number is required"
                            ),
                        address_line_2: yup
                            .string(),
                        city: yup.string().required("A city is required"),
                        county: yup.string().required("A county is required"),
                        post_code: yup
                            .string()
                            .required("A post code is required"),
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
                                Use a permanent address where you can recieve
                                post.
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
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                                <TextInput
                                    name="mobile"
                                    type="tel"
                                    label="Phone number"
                                />
                            </div>

                            <div className="col-span-6">
                                <TextInput
                                    name="address_line_1"
                                    label="Address line 1"
                                />
                            </div>

                            <div className="col-span-6">
                                <TextInput
                                    name="address_line_2"
                                    label="Address line 2"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-6 lg:col-span-2">
                                <TextInput name="city" label="City" />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                                <TextInput name="county" label="County" />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                                <TextInput name="post_code" label="Post Code" />
                            </div>

                            <div className="col-span-6 sm:col-span-3">
                                <Select
                                    name="country"
                                    options={Object.keys(props.countries).map(
                                        (code) => {
                                            return {
                                                key: code,
                                                name: props.countries[code],
                                            };
                                        }
                                    )}
                                    label="Country"
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
    <MainLayout title="My Account" subtitle="Manage your personal details">
        <Layout children={page} />
    </MainLayout>
);

export default Show;
