import React, { useEffect, useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Card from "@/Components/Card";
import {
    DefinitionList,
    DefinitionListItemProps,
} from "@/Components/DefinitionList";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import BasicList from "@/Components/BasicList";
import Link from "@/Components/Link";
import ButtonLink from "@/Components/ButtonLink";
import Button from "@/Components/Button";
import { PlusCircleIcon } from "@heroicons/react/24/outline";
import Form, { RenderServerErrors } from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import Modal from "@/Components/Modal";
import { VenueCombobox } from "@/Components/Venues/VenueCombobox";
import DateTimeInput, {
    DateTimeInputTimezones,
} from "@/Components/Form/DateTimeInput";
import formatISO from "date-fns/formatISO";
import FlashAlert from "@/Components/FlashAlert";
import ActionPanel from "@/Components/ActionPanel";
import { courseLength } from "@/Utils/Competitions/CourseLength";
import { competitionStatus } from "@/Utils/Competitions/CompetitionStatus";
import Select from "@/Components/Form/Select";
import Alert from "@/Components/Alert";

type Session = {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
};

export type Props = {
    google_maps_api_key: string;
    name: string;
    id: number;
    pool_course: string;
    closing_date: string;
    age_at_date: string;
    mode: string;
    public: boolean;
    require_times: boolean;
    requires_approval: boolean;
    status?: string;
    processing_fee: number;
    processing_fee_string: string;
    coach_enters: boolean;
    description: string;
    venue: {
        name: string;
        id: number;
        formatted_address: string;
        place_id: string;
    };
    sessions: Session[];
    editable: boolean;
    view_entries: boolean;
    members_can_enter: boolean;
    guests_can_enter: boolean;
    timezones: DateTimeInputTimezones;
    org_timezone: string;
    qfr?: {
        credits_formatted: string;
        debits_formatted: string;
        balance_formatted: string;
        credits: number;
        debits: number;
        balance: number;
    };
};

const Show: Layout<Props> = (props: Props) => {
    const [showAddSessionModal, setShowAddSessionModal] =
        useState<boolean>(false);

    const Session = (item: Session) => {
        return {
            id: item.id,
            content: (
                <>
                    <Link
                        className="flex md:flex-row items-center justify-between gap-y-3 text-sm"
                        key={item.id}
                        href={route("competitions.sessions.show", {
                            competition: props.id,
                            session: item.id,
                        })}
                    >
                        <div>
                            <div>
                                <strong>{item.name}</strong>
                            </div>
                            <div>
                                {formatDateTime(item.start_time)} -{" "}
                                {formatDateTime(item.end_time)}
                            </div>
                        </div>
                    </Link>
                </>
            ),
        };
    };

    const AddSessionForm = () => {
        const [time, setTime] = useState<string>(null);

        useEffect(() => {
            setTime(formatISO(new Date()));
        }, []);

        return (
            <Modal
                onClose={() => setShowAddSessionModal(false)}
                title="Add session"
                buttons={<></>}
                show={showAddSessionModal}
                Icon={PlusCircleIcon}
            >
                <Form
                    formName="new_session"
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A name is required for this session.")
                            .max(
                                255,
                                "The session name must be less than 255 characters."
                            ),
                        venue: yup
                            .number()
                            .required("A venue is required for this session."),
                        start_time: yup
                            .date()
                            .required(
                                "A start time is required for this session."
                            ),
                        timezone: yup.string().required(),
                        end_time: yup
                            .date()
                            .required(
                                "An end time is required for this session."
                            )
                            .min(
                                yup.ref("start_time"),
                                "The end time for this session must be after the start time."
                            ),
                    })}
                    initialValues={{
                        name: `Session ${props.sessions.length + 1}`,
                        venue: props.venue.id,
                        start_time: time,
                        timezone: props.org_timezone,
                        end_time: time,
                    }}
                    alwaysClearable
                    onClear={() => setShowAddSessionModal(false)}
                    onSuccess={() => setShowAddSessionModal(false)}
                    clearTitle="Cancel"
                    submitTitle="Add session"
                    method="post"
                    action={route("competitions.sessions.index", {
                        competition: props.id,
                    })}
                >
                    <RenderServerErrors />
                    <FlashAlert className="mb-4" />

                    <TextInput name="name" label="Name" />
                    <VenueCombobox name="venue" />
                    <DateTimeInput
                        name="start_time"
                        label="Start time"
                        showTimeInput
                    />
                    <DateTimeInput
                        name="end_time"
                        label="End time"
                        showTimeInput
                    />

                    <Select
                        name="timezone"
                        label="Session timezone"
                        items={props.timezones}
                    />
                </Form>
            </Modal>
        );
    };

    const items: DefinitionListItemProps[] = [
        {
            key: "closing_date",
            term: "Closing date",
            definition: formatDateTime(props.closing_date),
        },
        {
            key: "length",
            term: "Pool length",
            definition: courseLength(props.pool_course),
        },
        {
            key: "age_at_date",
            term: "Age at",
            definition: formatDate(props.age_at_date),
        },
        {
            key: "status",
            term: "Status",
            definition: competitionStatus(props.status),
        },
        {
            key: "require_times",
            term: "Requires times",
            definition: props.require_times ? "Yes" : "No",
        },
        {
            key: "requires_approval",
            term: "Entries require approval",
            definition: props.requires_approval ? "Yes" : "No",
        },
        {
            key: "coach_enters",
            term: "Coaches select swims",
            definition: props.coach_enters ? "Yes" : "No",
        },
    ];

    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle="Competition"
                    buttons={
                        <>
                            {props.editable && (
                                <ButtonLink
                                    href={route("competitions.edit", {
                                        competition: props.id,
                                    })}
                                >
                                    Edit
                                </ButtonLink>
                            )}
                        </>
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <FlashAlert className="mb-6" />

                <div className="grid grid-cols-12 gap-6">
                    {props.status !== "published" && (
                        <div className="col-start-1 col-span-full md:col-span-7">
                            <Alert
                                title="Competition not published"
                                variant="warning"
                            >
                                <p className="mb-3">
                                    This competition is currently in{" "}
                                    {props.status} state. Though you can enter
                                    as an admin, you'll need to make it{" "}
                                    <span className="font-bold">Published</span>{" "}
                                    before members or guests can enter.
                                </p>

                                <p>
                                    To do this, edit this competition and change
                                    the{" "}
                                    <span className="font-bold">
                                        Publication status
                                    </span>{" "}
                                    dropdown.
                                </p>
                            </Alert>
                        </div>
                    )}

                    {(props.members_can_enter || props.guests_can_enter) && (
                        <div className="col-start-1 col-span-full md:col-span-7 flex flex-col gap-6">
                            <ActionPanel
                                title="This competition is open for entries"
                                buttons={
                                    <>
                                        {props.members_can_enter && (
                                            <ButtonLink
                                                href=""
                                                className="mr-3"
                                            >
                                                Enter now
                                            </ButtonLink>
                                        )}
                                        {props.guests_can_enter && (
                                            <Link
                                                href={route(
                                                    "competitions.enter_as_guest",
                                                    props.id
                                                )}
                                            >
                                                Enter as guest{" "}
                                                <span aria-hidden="true">
                                                    {" "}
                                                    &rarr;
                                                </span>
                                            </Link>
                                        )}
                                    </>
                                }
                            >
                                <p>
                                    You can enter this competition until{" "}
                                    {formatDateTime(props.closing_date)}.
                                </p>
                            </ActionPanel>
                        </div>
                    )}

                    <div className="col-start-1 col-span-full md:col-span-7 flex flex-col gap-6">
                        <Card title="Basic details">
                            <DefinitionList items={items} verticalPadding={2} />
                        </Card>
                    </div>

                    <div className="col-start-1 col-span-full md:col-span-7 flex flex-col gap-6">
                        <Card
                            title="Schedule"
                            footer={
                                props.editable && (
                                    <Button
                                        onClick={() => {
                                            setShowAddSessionModal(true);
                                        }}
                                    >
                                        Add session
                                    </Button>
                                )
                            }
                        >
                            <BasicList items={props.sessions.map(Session)} />
                        </Card>
                    </div>

                    {props.view_entries && (
                        <div className="col-start-1 col-span-full md:col-span-7 flex flex-col gap-6">
                            <Card title="Entrants">
                                <div className="text-sm">
                                    <Link
                                        href={route(
                                            "competitions.guest_entries.index",
                                            [props.id]
                                        )}
                                    >
                                        View guest entries
                                    </Link>
                                </div>
                            </Card>
                        </div>
                    )}

                    {props.qfr && (
                        <div className="col-start-1 col-span-full md:col-span-7 text-sm">
                            <Card title="Financial Overiew">
                                <DefinitionList
                                    items={[
                                        {
                                            key: "balance",
                                            term: "Net position",
                                            definition:
                                                props.qfr.balance_formatted,
                                        },
                                        {
                                            key: "credits",
                                            term: "Credits",
                                            definition:
                                                props.qfr.credits_formatted,
                                        },
                                        {
                                            key: "debits",
                                            term: "Debits",
                                            definition:
                                                props.qfr.debits_formatted,
                                        },
                                    ]}
                                />
                            </Card>
                        </div>
                    )}

                    <div className="col-span-full row-span-6 md:row-start-1 md:col-start-8 md:col-span-5">
                        <Card title="Venue" subtitle={props.venue.name}>
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

                <AddSessionForm />
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
