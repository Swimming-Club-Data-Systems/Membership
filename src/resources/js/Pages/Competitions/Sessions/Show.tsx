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
import NativeDateInput from "@/Components/Form/NativeDateInput";
import DateTimeInput from "@/Components/Form/DateTimeInput";

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
    competition: { name: string; id: number };
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
    different_venue_to_competition_venue: boolean;
};

const Show: Layout<Props> = (props: Props) => {
    const Event = (item: Event) => {
        const deleteEvent = (session, event) => {};

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
                        {props.editable && (
                            <div className="block">
                                <>
                                    <Button
                                        variant="danger"
                                        className="ml-3"
                                        onClick={() => {
                                            deleteEvent(item.session, item.id);
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

    const [showAddEventModal, setShowAddEventModal] = useState(false);

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
                            competition: props.id,
                        },
                    },
                    {
                        name: "Sessions",
                        route: "competitions.sessions.index",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                    {
                        name: props.name,
                        route: "competitions.sessions.show",
                        routeParams: {
                            competition: props.competition.id,
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
                        <Button
                            variant="primary"
                            onClick={() => {
                                setShowAddEventModal(true);
                            }}
                            type="button"
                        >
                            Add event
                        </Button>
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid lg:grid-cols-12 gap-6">
                    <div className="md:col-start-1 md:col-span-7 flex flex-col gap-6">
                        {props.editable && (
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
                                    start_date: yup
                                        .date()
                                        .typeError("Start date must be a date.")
                                        .required(
                                            "A start date and time is required."
                                        ),
                                    end_date: yup
                                        .date()
                                        .typeError("End date must be a date.")
                                        .required(
                                            "An end date and time is required."
                                        )
                                        .min(
                                            yup.ref("start_date"),
                                            "End time must be after the start time."
                                        ),
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
                                            name="start_date"
                                            label="Start date and time"
                                            mb="mb-0"
                                        />
                                        <DateTimeInput
                                            name="end_date"
                                            label="End date and time"
                                            mb="mb-0"
                                        />
                                    </div>
                                </Card>
                            </Form>
                        )}

                        <Card
                            title="Events"
                            footer={
                                <Button
                                    variant="primary"
                                    onClick={() => {
                                        setShowAddEventModal(true);
                                    }}
                                    type="button"
                                >
                                    Add event
                                </Button>
                            }
                        >
                            {/*<RenderServerErrors />*/}
                            <FlashAlert className="mb-4" bag="manage_lines" />

                            {props.events.length > 0 && (
                                <BasicList items={props.events.map(Event)} />
                            )}

                            {props.events.length === 0 && <></>}
                        </Card>
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

                <Form
                    formName="new_event"
                    validationSchema={yup.object().shape({
                        name: yup.string().required().max(255),
                        category: yup
                            .string()
                            .required()
                            .oneOf([
                                "open",
                                "male",
                                "female",
                                "mixed",
                                "boy",
                                "girl",
                            ]),
                        stroke: yup
                            .string()
                            .required()
                            .oneOf([
                                "butterfly",
                                "backstroke",
                                "breaststroke",
                                "freestyle",
                                "medley",
                                "individual_medley",
                                "custom",
                            ]),
                        distance: yup.number().required().min(0),
                        units: yup
                            .string()
                            .required()
                            .oneOf(["metres", "yards", "feet"]),
                        entry_fee: yup.number().required().min(0),
                        processing_fee: yup.number().required().min(0),
                    })}
                    hideDefaultButtons
                    initialValues={{
                        name: "",
                        category: "",
                        stroke: "freestyle",
                        distance: 0,
                        units: "metres",
                        entry_fee: 0,
                        processing_fee: 0,
                    }}
                    alwaysClearable
                    onClear={() => setShowAddEventModal(false)}
                    clearTitle="Cancel"
                    submitTitle="Add event"
                >
                    <Modal
                        show={showAddEventModal}
                        title="Add event"
                        buttons={<SubmissionButtons />}
                        onClose={() => {
                            setShowAddEventModal(false);
                        }}
                        Icon={PlusCircleIcon}
                    >
                        <TextInput name="name" label="Name" />

                        <RadioGroup label="Category">
                            <div className="grid grid-cols-2 lg:grid-cols-3">
                                <Radio
                                    label="Open"
                                    name="category"
                                    value="open"
                                />
                                <Radio
                                    label="Male"
                                    name="category"
                                    value="male"
                                />
                                <Radio
                                    label="Female"
                                    name="category"
                                    value="female"
                                />
                                <Radio
                                    label="Mixed"
                                    name="category"
                                    value="mixed"
                                />
                                <Radio
                                    label="Boy"
                                    name="category"
                                    value="boy"
                                />
                                <Radio
                                    label="Girl"
                                    name="category"
                                    value="girl"
                                />
                            </div>
                        </RadioGroup>

                        <RadioGroup label="Stroke">
                            <div className="grid grid-cols-2 lg:grid-cols-3">
                                <Radio
                                    label="Butterfly"
                                    name="stroke"
                                    value="butterfly"
                                />
                                <Radio
                                    label="Backstroke"
                                    name="stroke"
                                    value="backstroke"
                                />
                                <Radio
                                    label="Breaststroke"
                                    name="stroke"
                                    value="breaststroke"
                                />
                                <Radio
                                    label="Freestyle"
                                    name="stroke"
                                    value="freestyle"
                                />
                                <Radio
                                    label="Individual Medley"
                                    name="stroke"
                                    value="individual_medley"
                                />
                                <Radio
                                    label="Medley"
                                    name="stroke"
                                    value="medley"
                                />
                                <Radio
                                    label="Other"
                                    name="stroke"
                                    value="other"
                                />
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
                                <Radio label="Feet" name="units" value="feet" />
                            </div>
                        </RadioGroup>

                        <DecimalInput
                            name="entry_fee"
                            label="Entry fee (£)"
                            precision={2}
                        />

                        <DecimalInput
                            name="processing_fee"
                            label="Processing fee (£)"
                            precision={2}
                        />
                    </Modal>
                </Form>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
