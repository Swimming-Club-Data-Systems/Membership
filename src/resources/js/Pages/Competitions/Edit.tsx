import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import TextArea from "@/Components/Form/TextArea";
import Checkbox from "@/Components/Form/Checkbox";
import DecimalInput from "@/Components/Form/DecimalInput";
import Radio from "@/Components/Form/Radio";
import Card from "@/Components/Card";
import RadioGroup from "@/Components/Form/RadioGroup";
import { VenueCombobox } from "@/Components/Venues/VenueCombobox";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Select from "@/Components/Form/Select";
import Link from "@/Components/Link";

export type Props = {
    name: string;
    id: number;
};

const CompetitionStatusSelectValues = [
    { value: "draft", name: "Draft" },
    { value: "published", name: "Published" },
    { value: "paused", name: "Paused" },
    { value: "closed", name: "Closed" },
    { value: "cancelled", name: "Cancelled" },
];

export const CompetitionOpenToSelectValues = [
    { value: "members", name: "Members", disabled: true },
    { value: "guests", name: "Guests" },
    { value: "members_and_guests", name: "Members and Guests", disabled: true },
];

const New: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={`Edit ${props.name}`}
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                    {
                        name: "Edit",
                        route: "competitions.edit",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={`Edit ${props.name}`}
                    subtitle={`Change competition details`}
                ></MainHeader>

                <Form
                    hideDefaultButtons
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A name is required.")
                            .max(255, "Name must not exceed 255 characters."),
                        description: yup.string(),
                        venue: yup
                            .number()
                            .typeError("You must choose a venue.")
                            .required("You must choose a venue."),
                        closing_date: yup.date().required(),
                        age_at_date: yup.date().required(),
                        default_entry_fee: yup.number().required().min(0),
                        processing_fee: yup.number().required().min(0),
                        require_times: yup.boolean(),
                        coach_enters: yup.boolean(),
                        requires_approval: yup.boolean(),
                        public: yup.boolean(),
                        pool_course: yup
                            .string()
                            .required()
                            .oneOf([
                                "short",
                                "long",
                                "open_water",
                                "irregular",
                                "not_applicable",
                            ]),
                        status: yup
                            .string()
                            .required()
                            .oneOf([
                                "draft",
                                "published",
                                "paused",
                                "closed",
                                "cancelled",
                            ]),
                        open_to: yup
                            .string()
                            .required()
                            .oneOf(["members", "guests", "members_and_guests"]),
                        custom_fields: yup
                            .string()
                            .optional()
                            .test(
                                "is-valid-json",
                                "Custom field description is not valid JSON.",
                                (value) => {
                                    if (value) {
                                        try {
                                            JSON.parse(value);
                                            return true;
                                        } catch {
                                            return false;
                                        }
                                    }
                                    return true;
                                },
                            ),
                    })}
                    initialValues={{
                        name: "",
                        description: "",
                        state: null,
                        venue: null,
                        pool_course: "short",
                        require_times: false,
                        coach_enters: false,
                        requires_approval: false,
                        public: true,
                        default_entry_fee: 0,
                        processing_fee: 0,
                        closing_date: "",
                        age_at_date: "",
                        status: "draft",
                        open_to: "members",
                        custom_fields: "",
                    }}
                    submitTitle="Save"
                    action={route("competitions.show", {
                        competition: props.id,
                    })}
                    method="put"
                >
                    <div className="grid gap-6">
                        <Card
                            title="Basic details"
                            footer={<SubmissionButtons />}
                        >
                            <TextInput name="name" label="Name" />
                            <VenueCombobox name="venue" />
                            <Select
                                name="status"
                                label="Publication status"
                                items={CompetitionStatusSelectValues}
                                help="Competitions won't be visible or open to users or guests until the competition state has been set to published."
                            />
                            <RadioGroup label="Pool length" name="pool_course">
                                <Radio label="Short course" value="short" />
                                <Radio label="Long course" value="long" />
                                <Radio label="Open water" value="open_water" />
                                <Radio label="Other" value="irregular" />
                                <Radio
                                    label="Not applicable"
                                    value="not_applicable"
                                />
                            </RadioGroup>
                            <TextArea
                                name="description"
                                label="Description"
                                help="You may use Markdown formatting in this field."
                            />
                            <DateTimeInput
                                name="closing_date"
                                label="Closing date"
                                showTimeInput
                                help="00:00 indicates the start of the day. Use 23:59 for the end of the day."
                            />
                            <DateTimeInput name="age_at_date" label="Ages at" />
                            <Checkbox
                                name="require_times"
                                label="Require times"
                            />
                            <Checkbox
                                readOnly={true}
                                name="coach_enters"
                                label="Coaches select swims"
                                help="This option will become available when members can use the new competition tools."
                            />
                            <Checkbox
                                readOnly={true}
                                name="requires_approval"
                                label="Entries require approval"
                                help="This option will become available when members can use the new competition tools."
                            />
                            <Select
                                readOnly={true}
                                name="open_to"
                                label="Open to"
                                items={CompetitionOpenToSelectValues}
                            />
                            <Checkbox
                                name="public"
                                label="Display publicly"
                                help="Whether to show information about this competition publicly on the membership system website."
                            />
                            <DecimalInput
                                name="default_entry_fee"
                                label="Default entry fee (£)"
                                help="The default entry fee to use when creating events for this competition. Changes won't be applied to existing events or entries."
                                precision={2}
                            />
                            <DecimalInput
                                name="processing_fee"
                                label="Processing fee (£)"
                                help="Processing fee per swimmer. To comply with the law on credit/debit card surcharges, you must charge this fee for any payment method you support - even cash or bank transfer.  Changes won't be applied to existing events or entries."
                                precision={2}
                            />

                            <TextArea
                                name="custom_fields"
                                label="Custom field description JSON"
                                help={
                                    <>
                                        Define custom fields for guest
                                        competition entries.{" "}
                                        <Link
                                            external
                                            href="https://docs.myswimmingclub.uk/docs/competitions/v2/managing-competitions/custom-form-fields"
                                        >
                                            Learn more about custom fields
                                        </Link>
                                        .
                                    </>
                                }
                                className="font-mono"
                            />
                        </Card>
                    </div>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
