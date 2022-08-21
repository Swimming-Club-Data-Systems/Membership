import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Layout from "./Layout";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import "yup-phone";
import Card from "@/Components/Card";

const Show = (props) => {
    console.log(props);
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
                    })}
                    // onSubmit={onSubmit}
                    submitTitle="Save"
                    hideClear
                    hideDefaultButtons
                    removeDefaultInputMargin
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

                            <div className="col-span-6 sm:col-span-3">
                                <label
                                    htmlFor="country"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Country
                                </label>
                                <select
                                    id="country"
                                    name="country"
                                    autoComplete="country-name"
                                    className="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                                    {
                                        Object.keys(props.countries).map(code => {
                                            return (<option key={code} value={code}>{props.countries[code]}</option>)
                                        })
                                    }
                                </select>
                            </div>

                            <div className="col-span-6">
                                <label
                                    htmlFor="street-address"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Street address
                                </label>
                                <input
                                    type="text"
                                    name="street-address"
                                    id="street-address"
                                    autoComplete="street-address"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-6 lg:col-span-2">
                                <label
                                    htmlFor="city"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    City
                                </label>
                                <input
                                    type="text"
                                    name="city"
                                    id="city"
                                    autoComplete="address-level2"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                                <label
                                    htmlFor="region"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    State / Province
                                </label>
                                <input
                                    type="text"
                                    name="region"
                                    id="region"
                                    autoComplete="address-level1"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                                <label
                                    htmlFor="postal-code"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    ZIP / Postal code
                                </label>
                                <input
                                    type="text"
                                    name="postal-code"
                                    id="postal-code"
                                    autoComplete="postal-code"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
