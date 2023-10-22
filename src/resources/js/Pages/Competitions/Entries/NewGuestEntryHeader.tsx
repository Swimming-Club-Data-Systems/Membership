import React, { ReactNode, useMemo } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";
import { FieldArray, useField } from "formik";
import Button from "@/Components/Button";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import { formatISO } from "date-fns";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import FlashAlert from "@/Components/FlashAlert";
import getCustomInitialValues from "@/Utils/Form/getCustomInitialValues";
import generateFields from "@/Utils/Form/generateFields";
import { Field } from "@/Utils/Form/Field";
import generateYupFields from "@/Utils/Form/generateYupFields";
import Link from "@/Components/Link";
import DateNumeralInput from "@/Components/Form/DateNumeralInput";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
    };
    user?: {
        first_name: string;
        last_name: string;
        email: string;
    };
    tenant: {
        name: string;
    };
    custom_fields: {
        guest_header_fields?: Field[];
        guest_entrant_fields?: Field[];
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
    const headerFields = props.custom_fields.guest_header_fields;
    const swimmerFields = props.custom_fields.guest_entrant_fields;

    const swimmerInitialValues = useMemo(() => {
        return {
            ...getCustomInitialValues(swimmerFields),
            first_name: "",
            last_name: "",
            date_of_birth: formatISO(new Date()),
            sex: "",
        };
    }, [swimmerFields]);

    const initialValues = useMemo(() => {
        return {
            ...getCustomInitialValues(headerFields),
            first_name: props.user?.first_name || "",
            last_name: props.user?.last_name || "",
            email: props.user?.email || "",
            swimmers: [swimmerInitialValues],
        };
    }, [
        props.user?.email,
        props.user?.first_name,
        props.user?.last_name,
        headerFields,
        swimmerInitialValues,
    ]);

    const validationSchema = useMemo(() => {
        return yup.object().shape({
            ...generateYupFields(headerFields),
            first_name: yup
                .string()
                .required("A first name is required.")
                .max(50, "First name must be at most 50 characters."),
            last_name: yup
                .string()
                .required("A last name is required.")
                .max(50, "Last name must be at most 50 characters."),
            email: yup
                .string()
                .required("An email address is required")
                .email("A valid email address is required"),
            swimmers: yup.array().of(
                yup.object().shape({
                    ...generateYupFields(swimmerFields),
                    first_name: yup
                        .string()
                        .required("A first name is required.")
                        .max(50, "First name must be at most 50 characters."),
                    last_name: yup
                        .string()
                        .required("A last name is required.")
                        .max(50, "Last name must be at most 50 characters."),
                    date_of_birth: yup
                        .date()
                        .typeError("A valid date is required.")
                        .required("A date of birth is required.")
                        .min(
                            "1900-01-01",
                            "Date of birth must be at least 1 January 1900."
                        )
                        .max(new Date(), "Date of birth must be in the past."),
                    sex: yup
                        .string()
                        .required("A sex is required.")
                        .oneOf(
                            ["Male", "Female"],
                            "Sex must be Female or Open."
                        ),
                })
            ),
        });
    }, [headerFields, swimmerFields]);

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

            <Container>
                <MainHeader
                    title={"Guest Entry"}
                    subtitle={`Enter a competition as a guest`}
                ></MainHeader>
            </Container>

            <Form
                removeDefaultInputMargin={true}
                hideDefaultButtons
                validationSchema={validationSchema}
                initialValues={initialValues}
                submitTitle="Next step"
                action={route(
                    "competitions.enter_as_guest",
                    props.competition.id
                )}
                method="post"
                hideErrors
                enableReinitialize={false}
            >
                <Container noMargin>
                    <FlashAlert />
                    <RenderServerErrors />

                    <div className="grid gap-6">
                        <Card
                            title="Tell us about yourself"
                            subtitle="You'll tell us about your swimmers in the next step."
                        >
                            <div className="grid grid-cols-12 gap-4">
                                <div className="col-start-1 col-end-7 md:col-start-1 md:col-end-5">
                                    <TextInput
                                        name="first_name"
                                        label="First name"
                                        autoComplete="given-name"
                                        readOnly={Boolean(props.user)}
                                    />
                                </div>
                                <div className="col-start-7 col-end-13 md:col-start-5 md:col-end-9">
                                    <TextInput
                                        name="last_name"
                                        label="Last name"
                                        autoComplete="family-name"
                                        readOnly={Boolean(props.user)}
                                    />
                                </div>
                                <div className="col-start-1 col-end-13 md:col-start-1 md:col-end-7">
                                    <TextInput
                                        name="email"
                                        label="Email address"
                                        autoComplete="email"
                                        readOnly={Boolean(props.user)}
                                    />
                                </div>
                                {generateFields(headerFields)}
                            </div>
                        </Card>

                        <FieldArray name="swimmers">
                            {({ insert, remove, push }) => (
                                <Card
                                    title="Swimmer details"
                                    subtitle="Tell us about your swimmers."
                                    footer={
                                        <Button
                                            onClick={() =>
                                                push(swimmerInitialValues)
                                            }
                                        >
                                            Add another swimmer
                                        </Button>
                                    }
                                >
                                    <FieldArrayItems
                                        name="swimmers"
                                        render={(index, count) => (
                                            <>
                                                {count > 1 && (
                                                    <div className="flex justify-between items-center mb-3">
                                                        <h4 className="font-semibold text-gray-900">
                                                            Swimmer {index + 1}
                                                        </h4>

                                                        <Button
                                                            variant="danger"
                                                            onClick={() => {
                                                                remove(index);
                                                            }}
                                                        >
                                                            Remove swimmer
                                                        </Button>
                                                    </div>
                                                )}
                                                <div className="grid grid-cols-12 gap-4 mb-4">
                                                    <div className="col-start-1 col-end-7 md:col-start-1 md:col-end-5">
                                                        <TextInput
                                                            label="First name"
                                                            name={`swimmers[${index}].first_name`}
                                                        />
                                                    </div>
                                                    <div className="col-start-7 col-end-13 md:col-start-5 md:col-end-9">
                                                        <TextInput
                                                            label="Last name"
                                                            name={`swimmers[${index}].last_name`}
                                                        />
                                                    </div>
                                                    <div className="col-start-1 col-end-13">
                                                        <DateNumeralInput
                                                            name={`swimmers[${index}].date_of_birth`}
                                                            label="Date of birth"
                                                            min="1900-01-01"
                                                            max={formatISO(
                                                                new Date()
                                                            )}
                                                        />
                                                    </div>
                                                    <div className="col-start-1 col-end-13 md:col-start-1 md:col-end-9">
                                                        <RadioGroup
                                                            label="Sex"
                                                            name={`swimmers[${index}].sex`}
                                                        >
                                                            <Radio
                                                                value="Female"
                                                                label="Female"
                                                            />
                                                            <Radio
                                                                value="Male"
                                                                label="Open"
                                                                help="For athletes with a birth sex of male, trans or non-binary competitors and any competitor not eligible for the female category."
                                                            />
                                                        </RadioGroup>
                                                    </div>
                                                    {generateFields(
                                                        swimmerFields,
                                                        `swimmers[${index}]`
                                                    )}
                                                </div>

                                                {count > 1 &&
                                                    index < count - 1 && (
                                                        <hr className="border-gray-300 my-5" />
                                                    )}
                                            </>
                                        )}
                                    />
                                </Card>
                            )}
                        </FieldArray>

                        <Card title={`${props.tenant.name} and your data`}>
                            <div className="prose prose-sm">
                                <p>
                                    By continuing, you consent to the storage
                                    and use of your personal data by{" "}
                                    {props.tenant.name} for the purposes of
                                    processing your entry. You may request that{" "}
                                    {props.tenant.name} delete your personally
                                    identifiable data at any time. Personally
                                    identifiable data will be automatically
                                    deleted 3 months after the end of the
                                    competition you are entering. All data will
                                    also be deleted 24 hours after starting this
                                    entry if you don't complete payment.
                                </p>

                                <p>
                                    By proceeding, you also confirm that you
                                    accept the{" "}
                                    <Link
                                        href="/privacy"
                                        target="_blank"
                                        external
                                    >
                                        {props.tenant.name} terms and conditions
                                        relating to use of their services,
                                        competition entries and more
                                    </Link>
                                    .
                                </p>

                                <p>
                                    Use of this software is subject to the
                                    Swimming Club Data Systems (SCDS) terms and
                                    conditions, license agreements and
                                    responsible use policies, details of which
                                    can be found on the SCDS website. SCDS
                                    reserves the right to make changes to these
                                    terms and policies at any time.
                                </p>
                            </div>
                        </Card>
                    </div>
                </Container>

                <Container>
                    <div className="mt-6">
                        <SubmissionButtons />
                    </div>
                </Container>
            </Form>
        </>
    );
};

NewGuestEntryHeader.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default NewGuestEntryHeader;
