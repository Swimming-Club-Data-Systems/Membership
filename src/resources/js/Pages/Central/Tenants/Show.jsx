import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
    UnknownError,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import TextInput from "@/Components/Form/TextInput";
import Checkbox from "@/Components/Form/Checkbox";

const Index = (props) => {
    return (
        <>
            <Head title={`Tenant Details - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Form
                    initialValues={{
                        name: "",
                        code: "",
                        email: "",
                        website: "",
                        verified: false,
                        domain: "",
                    }}
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A tenant name is required"),
                        code: yup
                            .string()
                            .required("A Swim England club code is required"),
                        email: yup
                            .string()
                            .required("An email address is required")
                            .email("You must use a valid email address"),
                        website: yup
                            .string()
                            .required("You must enter a club website address")
                            .url("You must use a valid website URL"),
                        verified: yup
                            .bool()
                            .oneOf(
                                [true, false],
                                "Must either be true or false"
                            ),
                        domain: yup
                            .string()
                            .required("You must enter a default domain"),
                    })}
                    action={route("central.tenants.show", props.id)}
                    submitTitle="Save"
                    hideClear
                    hideDefaultButtons
                    hideErrors
                    method="put"
                >
                    <Card
                        title="Tenant Information"
                        subtitle="Information about the tenant organisation and
                                settings"
                        footer={<SubmissionButtons />}
                    >
                        <RenderServerErrors />
                        <UnknownError />
                        <FlashAlert className="mb-4" />

                        <TextInput name="name" label="Tenant name" />
                        <TextInput name="code" label="Swim England club code" />
                        <TextInput name="email" label="Default email address" />
                        <TextInput name="website" label="Club website url" />
                        {props.editable && (
                            <>
                                <Checkbox
                                    name="verified"
                                    label="Tenant is verified"
                                />
                                <TextInput
                                    name="domain"
                                    label="Primary domain"
                                />
                            </>
                        )}
                    </Card>
                </Form>
            </div>
        </>
    );
};

Index.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Index;
