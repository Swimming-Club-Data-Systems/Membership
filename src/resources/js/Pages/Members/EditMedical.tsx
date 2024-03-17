import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import * as yup from "yup";
import Form, { RenderServerErrors } from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Checkbox from "@/Components/Form/Checkbox";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import TextArea from "@/Components/Form/TextArea";
import Link from "@/Components/Link";
import { useField } from "formik";
import "yup-phone";

type Props = {
    id: number;
    name: string;
    first_name: string;
    last_name: string;
    age: number;
    form_initial_values: {
        conditions: string;
        allergies: string;
        medication: string;
    };
    tenant: {
        name: string;
        id: number;
    };
    member_user: {
        id: number;
        name: string;
        is_current_user: boolean;
    };
};

type OptionalFieldsProps = {
    name: string;
    label: string;
};

const OptionalFields = (props: OptionalFieldsProps) => {
    const [{ value }] = useField(`${props.name}_yes_no`);
    return (
        <>
            <RadioGroup label={props.label} name={`${props.name}_yes_no`}>
                <Radio value="yes" label="Yes" />
                <Radio value="no" label="No" />
            </RadioGroup>

            {value === "yes" && (
                <TextArea
                    name={props.name}
                    label="Give details"
                    help={
                        <>
                            You may use{" "}
                            <Link
                                href="https://www.markdownguide.org/"
                                external
                            >
                                Markdown Formatting
                            </Link>{" "}
                            in this field.
                        </>
                    }
                />
            )}
        </>
    );
};

const EditMedical = (props: Props) => {
    const gpRules =
        props.age < 18
            ? {
                  consent: yup.boolean(),
                  gp_name: yup
                      .string()
                      .required("Please provide the GP's name.")
                      .max(255, "GP name must be less than 255 characters."),
                  gp_address: yup
                      .string()
                      .required("Please provide the GP's address.")
                      .max(1024, "GP name must be less than 1024 characters."),
                  gp_phone: yup
                      .string()
                      .required("Please provide the GP's phone number.")
                      .phone(
                          "GB",
                          true,
                          "Please provide a valid phone number.",
                      ),
              }
            : {};

    return (
        <>
            <Head
                title={`Edit medical notes for ${props.name}`}
                breadcrumbs={[
                    { name: "Members", route: "members.index" },
                    {
                        name: props.name,
                        route: "members.show",
                        routeParams: {
                            member: props.id,
                        },
                    },
                    {
                        name: "Medical notes",
                        route: "members.edit_medical",
                        routeParams: {
                            member: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={`Edit ${props.name}'s medical notes`}
                    subtitle="Member medical notes"
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <Form
                    initialValues={{
                        conditions_yes_no: props.form_initial_values.conditions
                            ? "yes"
                            : "no",
                        allergies_yes_no: props.form_initial_values.allergies
                            ? "yes"
                            : "no",
                        medication_yes_no: props.form_initial_values.medication
                            ? "yes"
                            : "no",
                    }}
                    validationSchema={yup.object().shape({
                        conditions: yup
                            .string()
                            .nullable()
                            .when("conditions_yes_no", {
                                is: "yes",
                                then: (schema) =>
                                    schema
                                        .required("Please provide details.")
                                        .max(
                                            2048,
                                            "Details must be less than 2048 characters.",
                                        ),
                            }),
                        allergies: yup
                            .string()
                            .nullable()
                            .when("allergies_yes_no", {
                                is: "yes",
                                then: (schema) =>
                                    schema
                                        .required("Please provide details.")
                                        .max(
                                            2048,
                                            "Details must be less than 2048 characters.",
                                        ),
                            }),
                        medication: yup
                            .string()
                            .nullable()
                            .when("medication_yes_no", {
                                is: "yes",
                                then: (schema) =>
                                    schema
                                        .required("Please provide details.")
                                        .max(
                                            2048,
                                            "Details must be less than 2048 characters.",
                                        ),
                            }),
                        ...gpRules,
                    })}
                    submitTitle="Save"
                    action={route("members.edit_medical", props.id)}
                    method="put"
                    removeDefaultInputMargin
                    hideErrors
                >
                    <RenderServerErrors />
                    <FlashAlert className="mb-3" />

                    <div className="grid gap-4">
                        <Card>
                            <OptionalFields
                                label={`Does ${props.first_name} have any specific medical conditions or disabilities?`}
                                name="conditions"
                            />
                        </Card>

                        <Card>
                            <OptionalFields
                                label={`Does ${props.first_name} have any allergies?`}
                                name="allergies"
                            />
                        </Card>

                        <Card>
                            <OptionalFields
                                label={`Does ${props.first_name} take any regular medication?`}
                                name="medication"
                            />
                        </Card>

                        {props.member_user && props.age < 18 && (
                            <Card
                                title="Consent for emergency medical treatment"
                                subtitle="For members under the age of 18"
                            >
                                <div className="prose prose-sm">
                                    <p>
                                        It may be essential at some time for the
                                        club to have the necessary authority to
                                        obtain any urgent medical treatment for
                                        App whilst they train, compete or take
                                        part in activities with{" "}
                                        {props.tenant.name}.
                                    </p>

                                    <p>
                                        If you wish to grant such authority,
                                        please complete the details below to
                                        give your consent.
                                    </p>

                                    <p>
                                        I, {props.member_user.name} being the
                                        parent/guardian of {props.name} hereby
                                        consent to the use of this information
                                        by {props.tenant.name} for the
                                        protection and safeguarding of my
                                        child’s health. I also give permission
                                        for the Coach, Team Manager or other
                                        Club Officer to give the immediate
                                        necessary authority on my behalf for any
                                        medical or surgical treatment
                                        recommended by competent medical
                                        authorities, where it would be contrary
                                        to my {props.name}'s interest, in the
                                        doctor’s medical opinion, for any delay
                                        to be incurred by seeking my personal
                                        consent.
                                    </p>

                                    <p>
                                        I understand that {props.tenant.name}{" "}
                                        may still have a lawful need to use this
                                        information for such purposes even if I
                                        later seek to withdraw this consent.
                                    </p>

                                    <p>
                                        {props.tenant.name} will use your
                                        personal data for the purpose of{" "}
                                        {props.name}'s involvement in training,
                                        activities or competitions with
                                        {props.tenant.name}.
                                    </p>

                                    <p>
                                        For further details of how we process
                                        your personal data or your child’s
                                        personal data please{" "}
                                        <Link href="/privacy" external>
                                            view our Privacy Policy
                                        </Link>{" "}
                                        (opens in new tab).
                                    </p>
                                </div>

                                <Checkbox
                                    name="consent"
                                    label={`I, ${props.member_user.name} consent and grant such authority`}
                                />

                                <TextInput name="gp_name" label="GP name" />

                                <TextArea
                                    name="gp_address"
                                    label="GP address"
                                />

                                <TextInput
                                    name="gp_phone"
                                    label="GP telephone number"
                                />
                            </Card>
                        )}
                    </div>
                </Form>
            </Container>
        </>
    );
};

EditMedical.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default EditMedical;
