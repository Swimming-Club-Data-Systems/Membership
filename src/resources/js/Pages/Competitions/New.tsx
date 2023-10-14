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
import formatISO from "date-fns/formatISO";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import DecimalInput from "@/Components/Form/DecimalInput";
import Radio from "@/Components/Form/Radio";
import Card from "@/Components/Card";
import RadioGroup from "@/Components/Form/RadioGroup";
import { useField } from "formik";
import { VenueCombobox } from "@/Components/Venues/VenueCombobox";
import Select from "@/Components/Form/Select";
import { CompetitionOpenToSelectValues } from "@/Pages/Competitions/Edit";

export type Props = {
    google_maps_api_key: string;
};

const LastDay = () => {
    const todaysDate = formatISO(Date.now(), {
        representation: "date",
    });

    const [{ value }] = useField("setup_type");

    if (value !== "basic") {
        return null;
    }

    return (
        <DateTimeInput name="gala_date" label="Final day" min={todaysDate} />
    );
};

const New: Layout<Props> = (props: Props) => {
    const todaysDate = formatISO(Date.now(), {
        representation: "date",
    });

    return (
        <>
            <Head
                title="New Competition"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: "New",
                        route: "competitions.new",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Create Competition"}
                    subtitle={`Add a new competition`}
                ></MainHeader>

                <Form
                    hideDefaultButtons
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A name is required.")
                            .max(255, "Name must not exceed 255 characters."),
                        description: yup.string(),
                        venue_select: yup
                            .number()
                            .typeError("You must choose a venue.")
                            .required("You must choose a venue."),
                        closing_date: yup
                            .date()
                            .required("A closing date is required.")
                            .min(
                                todaysDate,
                                "The closing date must be in the future."
                            ),
                        age_at_date: yup
                            .date()
                            .required("An age at date is required.")
                            .min(
                                todaysDate,
                                "The age at date must be in the future."
                            ),
                        default_entry_fee: yup.number().required().min(0),
                        processing_fee: yup.number().required().min(0),
                        require_times: yup.boolean(),
                        coach_enters: yup.boolean(),
                        requires_approval: yup.boolean(),
                        public: yup.boolean(),
                        setup_type: yup
                            .string()
                            .required()
                            .oneOf(["basic", "full"]),
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
                        gala_date: yup.date().when("setup_type", {
                            is: "basic",
                            then: (schema) => schema.required().min(todaysDate),
                            otherwise: (schema) => schema.notRequired(),
                        }),
                        open_to: yup
                            .string()
                            .required()
                            .oneOf(["members", "guests", "members_and_guests"]),
                    })}
                    initialValues={{
                        name: "",
                        description: "",
                        venue_select: null,
                        pool_course: "short",
                        require_times: false,
                        coach_enters: false,
                        requires_approval: false,
                        public: true,
                        default_entry_fee: 0,
                        processing_fee: 0,
                        closing_date: todaysDate,
                        age_at_date: todaysDate,
                        gala_date: todaysDate,
                        setup_type: "basic",
                        open_to: "members",
                    }}
                    submitTitle="Next step"
                    action={route("competitions.index")}
                    method="post"
                >
                    <div className="grid gap-6">
                        <Card
                            title="Basic details"
                            footer={<SubmissionButtons />}
                        >
                            <TextInput name="name" label="Name" />
                            <VenueCombobox name="venue_select" />
                            <RadioGroup label="Pool length">
                                <Radio
                                    name="pool_course"
                                    label="Short course"
                                    value="short"
                                />
                                <Radio
                                    name="pool_course"
                                    label="Long course"
                                    value="long"
                                />
                                <Radio
                                    name="pool_course"
                                    label="Open water"
                                    value="open_water"
                                />
                                <Radio
                                    name="pool_course"
                                    label="Other"
                                    value="irregular"
                                />
                                <Radio
                                    name="pool_course"
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
                                min={todaysDate}
                                showTimeInput
                            />
                            <DateTimeInput
                                name="age_at_date"
                                label="Ages at"
                                min={todaysDate}
                            />
                            <Checkbox
                                name="require_times"
                                label="Require times"
                            />
                            <Checkbox
                                name="coach_enters"
                                label="Coaches select swims"
                            />
                            <Checkbox
                                name="requires_approval"
                                label="Entries require approval"
                            />
                            <Select
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
                                help="The default entry fee to use when creating events for this competition."
                                precision={2}
                            />
                            <DecimalInput
                                name="processing_fee"
                                label="Processing fee (£)"
                                help="Processing fee per swimmer. To comply with the law on credit/debit card surcharges, you must charge this fee for any payment method you support - even cash or bank transfer."
                                precision={2}
                            />
                            <RadioGroup label="How do you want to configure this competition?">
                                <Radio
                                    name="setup_type"
                                    label="Basic"
                                    value="basic"
                                    help="No sessions, created with default events. Remove events which don't apply."
                                />
                                <Radio
                                    name="setup_type"
                                    label="Full"
                                    value="full"
                                    help="Configure all sessions and events manually."
                                />
                            </RadioGroup>
                            <LastDay />
                        </Card>
                    </div>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
