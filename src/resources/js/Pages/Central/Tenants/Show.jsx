import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/react";
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
import Fieldset from "@/Components/Form/Fieldset";
import { useField } from "formik";
import Radio from "@/Components/Form/Radio";

const ApplicationFeeAmount = () => {
    const [field] = useField("application_fee_type");

    if (field.value === "none") {
        return null;
    }

    return (
        <TextInput
            name="application_fee_amount"
            label={
                field.value === "fixed"
                    ? "Application fee amount"
                    : "Application fee percent"
            }
            type="number"
            min="0"
            step="0.01"
        />
    );
};

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
                        alphanumeric_sender_id: "",
                        application_fee_type: "none",
                        application_fee_amount: 0,
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
                        alphanumeric_sender_id: yup
                            .string()
                            .max(
                                11,
                                "Alphanumeric sender IDs may not exceed 11 characters"
                            ),
                        application_fee_type: yup
                            .string()
                            .required()
                            .oneOf(["none", "fixed", "percent"]),
                        application_fee_amount: yup
                            .number()
                            .nullable()
                            .when("application_fee_type", {
                                is: (val) =>
                                    val === "fixed" || val === "percent",
                                then: (schema) => schema.min(0).required(),
                            }),
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
                        <TextInput
                            name="email"
                            label="Default email address"
                            help="This is where replies to system emails will go unless otherwise specified."
                        />
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
                        <TextInput
                            name="alphanumeric_sender_id"
                            label="SMS Sender ID"
                            help="The from name to use on SMS messages sent by the membership system. If you leave this blank, we'll use SWIM CLUB as the sender name."
                            maxLength={11}
                        />
                        {props.editable && (
                            <Fieldset legend="Application fee type">
                                <Radio
                                    name="application_fee_type"
                                    value="none"
                                    label="None"
                                />
                                <Radio
                                    name="application_fee_type"
                                    value="percent"
                                    label="Percent"
                                />
                                <Radio
                                    name="application_fee_type"
                                    value="fixed"
                                    label="Fixed"
                                />

                                <ApplicationFeeAmount />
                            </Fieldset>
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
