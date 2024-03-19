import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { EntryForm, Session } from "@/Components/Competitions/EntryForm";
import Alert from "@/Components/Alert";
import ButtonLink from "@/Components/ButtonLink";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
        require_times: boolean;
    };
    header: {
        id: string;
    };
    id: string;
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
    sessions: Session[];
    paid: boolean;
};

const EditGuestEntry: Layout<Props> = (props: Props) => {
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
                    {
                        name: "Guest Entry",
                        route: "competitions.enter_as_guest.show",
                        routeParams: {
                            competition: props.competition.id,
                            header: props.header.id,
                        },
                    },
                    {
                        name: "Select Events",
                        route: "competitions.enter_as_guest.edit_entry",
                        routeParams: {
                            competition: props.competition.id,
                            header: props.header.id,
                            entrant: props.entrant.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={`Manage ${props.entrant.first_name}'s entry`}
                    subtitle={`For ${props.competition.name}.`}
                    buttons={
                        <ButtonLink
                            href={route("competitions.enter_as_guest.show", [
                                props.competition.id,
                                props.header.id,
                            ])}
                        >
                            Back
                        </ButtonLink>
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    {props.paid && (
                        <Alert
                            title="Entry locked"
                            variant="warning"
                            className="mb-4"
                        >
                            This entry has been paid for and can now no longer
                            be amended. You must contact {props.tenant.name}{" "}
                            directly if you need to make any changes.
                        </Alert>
                    )}
                </div>
            </Container>

            <EntryForm
                requireTimes={props.competition.require_times}
                sessions={props.sessions}
                action={route("competitions.enter_as_guest.edit_entry", {
                    competition: props.competition.id,
                    header: props.id,
                    entrant: props.entrant.id,
                })}
                readOnly={props.paid}
            />
        </>
    );
};

EditGuestEntry.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default EditGuestEntry;
