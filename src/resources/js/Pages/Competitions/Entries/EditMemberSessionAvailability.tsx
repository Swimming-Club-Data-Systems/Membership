import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Alert from "@/Components/Alert";
import ButtonLink from "@/Components/ButtonLink";
import FlashAlert from "@/Components/FlashAlert";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import Checkbox from "@/Components/Form/Checkbox";
import EmptyState from "@/Components/EmptyState";
import { formatDateTime } from "@/Utils/date-utils";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
        require_times: boolean;
        processing_fee: number;
        processing_fee_formatted: string;
    };
    first_name: string;
    last_name: string;
    email: string;
    entrant: {
        id: string;
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
    };
    tenant: {
        name: string;
    };
    sessions: {
        id: number;
        name: string;
        start_time: string;
        end_time: string;
        timezone: string;
        events: {
            id: number;
            name: string;
            entry_fee: number;
            processing_fee: number;
            entry_fee_formatted: string;
            processing_fee_formatted: string;
        }[];
    }[];
    paid: boolean;
};

const EditMemberSessionAvailability: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Entry"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    {
                        name: "Select Availability",
                        route: "competitions.enter.edit_entry",
                        routeParams: {
                            competition: props.competition.id,
                            member: props.entrant.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={`Manage ${props.entrant.first_name}'s session availability`}
                    subtitle={`For ${props.competition.name}.`}
                    buttons={
                        <ButtonLink
                            href={route("competitions.show", [
                                props.competition.id,
                            ])}
                        >
                            Back
                        </ButtonLink>
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <FlashAlert className="mb-4" />
                </div>

                <Form
                    validationSchema={yup.object().shape({})}
                    initialValues={{}}
                >
                    <div className="grid gap-4">
                        {props.sessions.length === 0 && (
                            <EmptyState>
                                We are sorry but there are no sessions available
                                for {props.entrant.first_name} to enter.
                            </EmptyState>
                        )}

                        {props.sessions.map((session, idx) => {
                            return (
                                <Card
                                    key={session.id}
                                    title={session.name}
                                    subtitle={`${formatDateTime(
                                        session.start_time,
                                    )} - ${formatDateTime(session.end_time)}`}
                                >
                                    {session.events.length > 0 && (
                                        <>
                                            <div className="text-sm">
                                                <p className="mb-3">
                                                    The following events are
                                                    available to{" "}
                                                    {props.entrant.first_name}{" "}
                                                    in this session;
                                                </p>

                                                <ul className="list-decimal list-inside">
                                                    {session.events.map(
                                                        (ev) => {
                                                            return (
                                                                <li>
                                                                    {ev.name},{" "}
                                                                    {
                                                                        ev.entry_fee_formatted
                                                                    }
                                                                    {ev.processing_fee >
                                                                        0 && (
                                                                        <>
                                                                            {" "}
                                                                            (plus{" "}
                                                                            {
                                                                                ev.entry_fee_formatted
                                                                            }{" "}
                                                                            processing
                                                                            fee)
                                                                        </>
                                                                    )}
                                                                </li>
                                                            );
                                                        },
                                                    )}
                                                </ul>
                                            </div>

                                            <Checkbox
                                                name={`sessions[${idx}].available`}
                                                label="Available to enter"
                                            />
                                        </>
                                    )}

                                    {session.events.length === 0 && (
                                        <Alert
                                            variant="warning"
                                            title="No events"
                                        >
                                            There are no events open to{" "}
                                            {props.entrant.first_name} during{" "}
                                            {session.name}
                                        </Alert>
                                    )}
                                </Card>
                            );
                        })}

                        {props.competition.processing_fee > 0 && (
                            <Card title="Additional fees">
                                <div className="prose prose-sm">
                                    <p>
                                        An additional processing fee of{" "}
                                        {
                                            props.competition
                                                .processing_fee_formatted
                                        }{" "}
                                        applies to this competition. This is
                                        separate from any individual event
                                        processing fees.
                                    </p>
                                </div>
                            </Card>
                        )}
                    </div>
                </Form>
            </Container>
        </>
    );
};

EditMemberSessionAvailability.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default EditMemberSessionAvailability;
