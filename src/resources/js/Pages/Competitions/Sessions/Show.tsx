import React, { useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Card from "@/Components/Card";
import Button from "@/Components/Button";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import FlashAlert from "@/Components/FlashAlert";
import BasicList from "@/Components/BasicList";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import DecimalInput from "@/Components/Form/DecimalInput";
import Modal from "@/Components/Modal";
import { PlusCircleIcon } from "@heroicons/react/24/outline";
import { VenueCombobox } from "@/Components/Venues/VenueCombobox";
import Alert from "@/Components/Alert";
import DateTimeInput, {
    DateTimeInputTimezones,
} from "@/Components/Form/DateTimeInput";
import { router } from "@inertiajs/react";
import ButtonLink from "@/Components/ButtonLink";
import {
    DefinitionList,
    DefinitionListItemProps,
} from "@/Components/DefinitionList";
import { formatDateTime } from "@/Utils/date-utils";
import Select from "@/Components/Form/Select";
import { FieldArray, useFormikContext } from "formik";

const getCategoryName = (category: string): string => {
    switch (category) {
        case "open":
            return "Open";
        case "male":
            return "Male";
        case "female":
            return "Female";
        case "mixed":
            return "Mixed";
        case "boy":
            return "Boys";
        case "girl":
            return "Girls";
    }
};

interface Event {
    session: number;
    id: number;
    name: string;
    ages: string[];
    category: string;
    created_at: string;
    updated_at: string;
    distance: number;
    entry_fee: number;
    entry_fee_string: string;
    event_code: number;
    processing_fee: number;
    processing_fee_string: string;
    sequence: number;
    stroke: string;
}

export type Props = {
    google_maps_api_key: string;
    name: string;
    id: number;
    competition: { name: string; id: number; default_entry_fee: string };
    venue: {
        name: string;
        id: number;
        formatted_address: string;
        place_id: string;
    };
    sequence_number: number;
    number_of_sessions: number;
    events: Event[];
    editable: boolean;
    edit_mode: boolean;
    different_venue_to_competition_venue: boolean;
    start_time: string;
    end_time: string;
    timezones: DateTimeInputTimezones;
};

const Ages = (props) => {
    const { values } = useFormikContext();

    return (
        <FieldArray
            name="ages"
            render={(arrayHelpers) => (
                <Card title="Age groups">
                    {values.ages &&
                        values.ages.length > 0 &&
                        values.ages.map((age, index) => (
                            <div key={index}>
                                <TextInput
                                    name={`ages.${index}`}
                                    label={`Age group ${index + 1}`}
                                    className="uppercase"
                                    rightButton={
                                        values.ages.length > 1 && (
                                            <Button
                                                className="rounded-l-none"
                                                variant="danger"
                                                onClick={() =>
                                                    arrayHelpers.remove(index)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        )
                                    }
                                    showErrorIconOnLabel // Required because of rightButton
                                />
                            </div>
                        ))}
                    <Button onClick={() => arrayHelpers.push("")}>
                        Add an age group
                    </Button>
                </Card>
            )}
        />
    );
};

const Show: Layout<Props> = (props: Props) => {
    const [deleteEvent, setDeleteEvent] = useState<Event>(null);
    const [showDeleteEventModal, setShowDeleteEventModal] =
        useState<boolean>(false);
    const [showAddEventModal, setShowAddEventModal] = useState<boolean>(false);
    const deleteOnClick = (item: Event) => {
        setDeleteEvent(item);
        setShowDeleteEventModal(true);
    };
    const confirmDeleteEvent = async () => {
        router.delete(
            route("competitions.sessions.events.delete", {
                competition: props.competition.id,
                session: props.id,
                event: deleteEvent.id,
            }),
            {
                preserveScroll: true,
                onFinish: () => {
                    setShowDeleteEventModal(false);
                },
            }
        );
    };
    const Event = (item: Event) => {
        return {
            id: item.id,
            content: (
                <>
                    <div
                        className="flex md:flex-row items-center justify-between gap-y-3 text-sm"
                        key={item.id}
                    >
                        <div className="">
                            <div className="text-gray-900">
                                <strong>
                                    {getCategoryName(item.category)}
                                </strong>{" "}
                                {item.name}
                            </div>
                            <div className="text-gray-500">
                                <>
                                    Age group{item.ages.length > 1 ? "s" : null}
                                    : {item.ages.join(", ")}
                                </>
                            </div>
                            <div className="text-gray-500">
                                <>
                                    £{item.entry_fee_string}
                                    {item.processing_fee > 0 && (
                                        <>
                                            {" "}
                                            plus £{
                                                item.processing_fee_string
                                            }{" "}
                                            processing fee
                                        </>
                                    )}
                                </>
                            </div>
                        </div>
                        {props.edit_mode && (
                            <div className="block">
                                <>
                                    <Button
                                        variant="danger"
                                        className="ml-3"
                                        onClick={() => {
                                            deleteOnClick(item);
                                        }}
                                    >
                                        Delete
                                    </Button>
                                </>
                            </div>
                        )}
                    </div>
                </>
            ),
        };
    };

    const items: DefinitionListItemProps[] = [
        {
            key: "number",
            term: "Session number",
            definition: `${props.sequence_number} of ${props.number_of_sessions}`,
        },
        {
            key: "name",
            term: "Session name",
            definition: props.name,
        },
        {
            key: "starts",
            term: "Starts at",
            definition: formatDateTime(props.start_time),
        },
        {
            key: "ends",
            term: "Ends at",
            definition: formatDateTime(props.end_time),
        },
    ];

    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    // {
                    //     name: "Sessions",
                    //     route: "competitions.sessions.index",
                    //     routeParams: {
                    //         competition: props.id,
                    //     },
                    // },
                    {
                        name: props.name,
                        route: "competitions.sessions.show",
                        routeParams: {
                            competition: props.id,
                            session: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle={`Session ${props.sequence_number} of ${props.number_of_sessions}`}
                    buttons={
                        props.editable && (
                            <>
                                {props.edit_mode && (
                                    <Button
                                        variant="primary"
                                        onClick={() => {
                                            setShowAddEventModal(true);
                                        }}
                                        type="button"
                                    >
                                        Add event
                                    </Button>
                                )}
                                {!props.edit_mode && (
                                    <ButtonLink
                                        href={route(
                                            "competitions.sessions.edit",
                                            {
                                                competition:
                                                    props.competition.id,
                                                session: props.id,
                                            }
                                        )}
                                        variant="primary"
                                    >
                                        Edit
                                    </ButtonLink>
                                )}
                            </>
                        )
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid lg:grid-cols-12 gap-6">
                    <div className="md:col-start-1 md:col-span-7 flex flex-col gap-6">
                        {props.edit_mode && (
                            <Form
                                initialValues={{}}
                                validationSchema={yup.object().shape({
                                    name: yup
                                        .string()
                                        .required(
                                            "A name is required for this session."
                                        )
                                        .max(
                                            255,
                                            "The session name must not exceed 255 characters."
                                        ),
                                    venue: yup.number().required().integer(),
                                    start_time: yup
                                        .date()
                                        .typeError("Start date must be a date.")
                                        .required(
                                            "A start date and time is required."
                                        ),
                                    end_time: yup
                                        .date()
                                        .typeError("End date must be a date.")
                                        .required(
                                            "An end date and time is required."
                                        )
                                        .min(
                                            yup.ref("start_time"),
                                            "End time must be after the start time."
                                        ),
                                    timezone: yup.string().required(),
                                })}
                                hideDefaultButtons
                                formName="edit_session"
                                submitTitle="Save"
                                method="put"
                                action={route("competitions.sessions.show", {
                                    competition: props.competition.id,
                                    session: props.id,
                                })}
                                hideErrors
                            >
                                <Card
                                    title="Session details"
                                    footer={<SubmissionButtons />}
                                >
                                    <RenderServerErrors />
                                    <TextInput name="name" label="Name" />
                                    <VenueCombobox name="venue" />
                                    <div className="grid md:grid-cols-2 gap-6">
                                        <DateTimeInput
                                            name="start_time"
                                            label="Start date and time"
                                            mb="mb-0"
                                            showTimeInput
                                        />
                                        <DateTimeInput
                                            name="end_time"
                                            label="End date and time"
                                            mb="mb-0"
                                            showTimeInput
                                        />
                                    </div>

                                    <Select
                                        name="timezone"
                                        label="Session timezone"
                                        items={props.timezones}
                                    />
                                </Card>
                            </Form>
                        )}

                        {!props.edit_mode && (
                            <Card title="Session details">
                                <DefinitionList
                                    items={items}
                                    verticalPadding={2}
                                />
                            </Card>
                        )}

                        <Card
                            title="Events"
                            footer={
                                props.edit_mode && (
                                    <Button
                                        variant="primary"
                                        onClick={() => {
                                            setShowAddEventModal(true);
                                        }}
                                        type="button"
                                    >
                                        Add event
                                    </Button>
                                )
                            }
                        >
                            {/*<RenderServerErrors />*/}
                            <FlashAlert className="mb-4" bag="manage_lines" />

                            {props.events.length > 0 && (
                                <BasicList items={props.events.map(Event)} />
                            )}

                            {props.events.length === 0 && (
                                <Alert variant="warning" title="No events">
                                    No events exist for this session.
                                </Alert>
                            )}
                        </Card>

                        <Modal
                            onClose={() => setShowAddEventModal(false)}
                            title="Add event"
                            buttons={<></>}
                            show={showAddEventModal}
                            Icon={PlusCircleIcon}
                        >
                            <Form
                                formName="new_event"
                                validationSchema={yup.object().shape({
                                    name: yup
                                        .string()
                                        .required(
                                            "A name is required for this event."
                                        )
                                        .max(
                                            255,
                                            "The event name must be less than 255 characters."
                                        ),
                                    category: yup
                                        .string()
                                        .required(
                                            "An event category is required."
                                        )
                                        .oneOf(
                                            [
                                                "open",
                                                "male",
                                                "female",
                                                "mixed",
                                                "boy",
                                                "girl",
                                            ],
                                            "The event category must be one of the supported types."
                                        ),
                                    stroke: yup
                                        .string()
                                        .required("A stroke is required.")
                                        .oneOf(
                                            [
                                                "butterfly",
                                                "backstroke",
                                                "breaststroke",
                                                "freestyle",
                                                "medley",
                                                "individual_medley",
                                                "custom",
                                            ],
                                            "The stroke must be one of the supported types."
                                        ),
                                    distance: yup
                                        .number()
                                        .required("Distance is required")
                                        .moreThan(
                                            0,
                                            "Distance must be more than 0."
                                        ),
                                    units: yup
                                        .string()
                                        .required(
                                            "A distance unit is required."
                                        )
                                        .oneOf(
                                            ["metres", "yards", "feet"],
                                            "The distance unit must be one of the supported types."
                                        ),
                                    entry_fee_string: yup
                                        .number()
                                        .required(
                                            "An entry fee is required, but may be £0."
                                        )
                                        .min(
                                            0,
                                            "Entry fee must be £0 or more."
                                        ),
                                    processing_fee_string: yup
                                        .number()
                                        .required(
                                            "An processing fee is required, but may be £0."
                                        )
                                        .min(
                                            0,
                                            "Processing fee must be £0 or more."
                                        ),
                                    ages: yup
                                        .array()
                                        .ensure()
                                        .of(
                                            yup
                                                .string()
                                                .required(
                                                    "An age group is required."
                                                )
                                                .test(
                                                    "is-valid-age-group-string",
                                                    "Age group must be OPEN or of the format X-, -Y or X-Y.",
                                                    (value) => {
                                                        if (
                                                            value.toUpperCase() ===
                                                            "OPEN"
                                                        ) {
                                                            return true;
                                                        }

                                                        // Check if single value
                                                        const singleValueExpression =
                                                            /^\d+$/;
                                                        if (
                                                            singleValueExpression.test(
                                                                value
                                                            )
                                                        ) {
                                                            return true;
                                                        }

                                                        // Check if X-, -Y or X-Y
                                                        const rangeExpression =
                                                            /^\d*-\d*$/;
                                                        if (
                                                            rangeExpression.test(
                                                                value
                                                            )
                                                        ) {
                                                            // Regex valid, check second number greater than first

                                                            // Split and convert values to array
                                                            const values = value
                                                                .split("-")
                                                                .filter(
                                                                    (v) =>
                                                                        v.length >
                                                                        0
                                                                )
                                                                .map((v) =>
                                                                    parseInt(v)
                                                                );
                                                            if (
                                                                values.length >
                                                                1
                                                            ) {
                                                                // If two values check first less than eq second
                                                                return (
                                                                    values[0] <=
                                                                    values[1]
                                                                );
                                                            } else {
                                                                // Only one value so valid
                                                                return true;
                                                            }
                                                        } else {
                                                            return false;
                                                        }
                                                    }
                                                )
                                        )
                                        .min(1),
                                })}
                                // hideDefaultButtons
                                initialValues={{
                                    name: "",
                                    category: "",
                                    stroke: "freestyle",
                                    distance: 0,
                                    units: "metres",
                                    entry_fee_string:
                                        props.competition.default_entry_fee,
                                    processing_fee_string: 0,
                                    ages: ["OPEN"],
                                }}
                                alwaysClearable
                                onClear={() => setShowAddEventModal(false)}
                                clearTitle="Cancel"
                                submitTitle="Add event"
                                method="post"
                                action={route(
                                    "competitions.sessions.events.create",
                                    {
                                        competition: props.competition.id,
                                        session: props.id,
                                    }
                                )}
                            >
                                {/*<Card*/}
                                {/*    show={showAddEventModal}*/}
                                {/*    title="Add event"*/}
                                {/*    footer={<SubmissionButtons />}*/}
                                {/*    Icon={PlusCircleIcon}*/}
                                {/*>*/}
                                <TextInput name="name" label="Name" />

                                <RadioGroup label="Category" name="category">
                                    <div className="grid grid-cols-2 lg:grid-cols-3">
                                        <Radio label="Open" value="open" />
                                        <Radio label="Male" value="male" />
                                        <Radio label="Female" value="female" />
                                        <Radio label="Mixed" value="mixed" />
                                        <Radio label="Boy" value="boy" />
                                        <Radio label="Girl" value="girl" />
                                    </div>
                                </RadioGroup>

                                <RadioGroup label="Stroke" name="stroke">
                                    <div className="grid grid-cols-2 lg:grid-cols-3">
                                        <Radio
                                            label="Butterfly"
                                            value="butterfly"
                                        />
                                        <Radio
                                            label="Backstroke"
                                            value="backstroke"
                                        />
                                        <Radio
                                            label="Breaststroke"
                                            value="breaststroke"
                                        />
                                        <Radio
                                            label="Freestyle"
                                            value="freestyle"
                                        />
                                        <Radio
                                            label="Individual Medley"
                                            value="individual_medley"
                                        />
                                        <Radio label="Medley" value="medley" />
                                        <Radio label="Other" value="other" />
                                    </div>
                                </RadioGroup>

                                <DecimalInput
                                    name="distance"
                                    label="Distance"
                                    precision={0}
                                />

                                <RadioGroup label="Units">
                                    <div className="grid grid-cols-2 lg:grid-cols-3">
                                        <Radio
                                            label="Metres"
                                            name="units"
                                            value="metres"
                                        />
                                        <Radio
                                            label="Yards"
                                            name="units"
                                            value="yards"
                                        />
                                        <Radio
                                            label="Feet"
                                            name="units"
                                            value="feet"
                                        />
                                    </div>
                                </RadioGroup>

                                <DecimalInput
                                    name="entry_fee_string"
                                    label="Entry fee (£)"
                                    precision={2}
                                />

                                <DecimalInput
                                    name="processing_fee_string"
                                    label="Processing fee (£)"
                                    precision={2}
                                />

                                <Ages />
                            </Form>
                        </Modal>
                    </div>
                    <div className="md:row-start-1 md:col-start-8 md:col-span-5">
                        <Card title="Venue" subtitle={props.venue.name}>
                            {props.different_venue_to_competition_venue && (
                                <Alert title="Please note" variant="warning">
                                    This session is at a different venue than
                                    the default venue for this competition.
                                </Alert>
                            )}

                            <p className="text-sm">
                                {props.venue.formatted_address}
                            </p>
                            <iframe
                                title="Map"
                                width="100%"
                                height="400"
                                style={{ border: 0 }}
                                loading="lazy"
                                allowFullScreen
                                referrerPolicy="no-referrer-when-downgrade"
                                src={`https://www.google.com/maps/embed/v1/place?key=${encodeURIComponent(
                                    props.google_maps_api_key
                                )}&q=place_id:${encodeURIComponent(
                                    props.venue.place_id
                                )}`}
                            ></iframe>
                        </Card>
                    </div>
                </div>

                <Modal
                    show={showDeleteEventModal}
                    onClose={() => setShowDeleteEventModal(false)}
                    variant="danger"
                    title="Delete event"
                    buttons={
                        <>
                            <Button
                                variant="danger"
                                onClick={confirmDeleteEvent}
                            >
                                Confirm
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() => setShowDeleteEventModal(false)}
                            >
                                Cancel
                            </Button>
                        </>
                    }
                >
                    {deleteEvent && (
                        <p>
                            Are you sure you want to delete {deleteEvent.name}?
                        </p>
                    )}
                </Modal>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
