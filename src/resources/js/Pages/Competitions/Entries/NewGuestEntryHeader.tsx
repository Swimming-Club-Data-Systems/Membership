import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";
import { FieldArray, useField } from "formik";
import Button from "@/Components/Button";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
    };
};

type FieldArrayItemsProps = {
    name: string;
    render: (index: number, length: number) => ReactNode;
};

const FieldArrayItems = ({ name, render }: FieldArrayItemsProps) => {
    const [field] = useField(name);

    return (
        <div>
            {field.value.map((item, idx) => render(idx, field.value.length))}
        </div>
    );
};

const NewGuestEntryHeader: Layout<Props> = (props: Props) => {
    const swimmerInitialValues = {
        first_name: "",
        last_name: "",
    };

    return (
        <>
            <Head
                title="Guest Entry"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    { name: "Guest Entry", route: "competitions.index" },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Guest Entry"}
                    subtitle={`Enter a competition as a guest`}
                ></MainHeader>

                <Form
                    removeDefaultInputMargin={true}
                    hideDefaultButtons
                    validationSchema={yup.object().shape({
                        first_name: yup
                            .string()
                            .required("A first name is required.")
                            .max(
                                50,
                                "First name must be at most 50 characters."
                            ),
                        last_name: yup
                            .string()
                            .required("A last name is required.")
                            .max(
                                50,
                                "Last name must be at most 50 characters."
                            ),
                        email: yup
                            .string()
                            .required("An email address is required")
                            .email("A valid email address is required"),
                        swimmers: yup.array().of(
                            yup.object().shape({
                                first_name: yup
                                    .string()
                                    .required("A first name is required.")
                                    .max(
                                        50,
                                        "First name must be at most 50 characters."
                                    ),
                                last_name: yup
                                    .string()
                                    .required("A last name is required.")
                                    .max(
                                        50,
                                        "Last name must be at most 50 characters."
                                    ),
                            })
                        ),
                    })}
                    initialValues={{
                        first_name: "",
                        last_name: "",
                        email: "",
                        swimmers: [swimmerInitialValues],
                    }}
                    submitTitle="Next step"
                    action={route(
                        "competitions.enter_as_guest",
                        props.competition.id
                    )}
                    method="post"
                >
                    <div className="grid gap-6">
                        <Card
                            title="Tell us about yourself"
                            subtitle="You'll tell us about your swimmers in the next step"
                        >
                            <div className="grid grid-cols-2 gap-4">
                                <TextInput
                                    name="first_name"
                                    label="First name"
                                    autoComplete="given-name"
                                />
                                <TextInput
                                    name="last_name"
                                    label="Last name"
                                    autoComplete="family-name"
                                />
                            </div>
                            <TextInput
                                name="email"
                                label="Email address"
                                autoComplete="email"
                            />
                        </Card>

                        <FieldArray name="swimmers">
                            {({ insert, remove, push }) => (
                                <Card
                                    title="Swimmer details"
                                    footer={
                                        <Button
                                            onClick={() =>
                                                push(swimmerInitialValues)
                                            }
                                        >
                                            Add swimmer
                                        </Button>
                                    }
                                >
                                    <FieldArrayItems
                                        name="swimmers"
                                        render={(index, count) => (
                                            <>
                                                <div className="grid grid-cols-2 gap-4">
                                                    <TextInput
                                                        label="First name"
                                                        name={`swimmers[${index}].first_name`}
                                                    />
                                                    <TextInput
                                                        label="Last name"
                                                        name={`swimmers[${index}].last_name`}
                                                    />
                                                    {count > 1 && (
                                                        <div>
                                                            <Button
                                                                variant="danger"
                                                                onClick={() => {
                                                                    remove(
                                                                        index
                                                                    );
                                                                }}
                                                            >
                                                                Remove
                                                            </Button>
                                                        </div>
                                                    )}
                                                </div>
                                            </>
                                        )}
                                    />
                                </Card>
                            )}
                        </FieldArray>

                        <SubmissionButtons />
                    </div>
                </Form>
            </Container>
        </>
    );
};

NewGuestEntryHeader.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default NewGuestEntryHeader;
