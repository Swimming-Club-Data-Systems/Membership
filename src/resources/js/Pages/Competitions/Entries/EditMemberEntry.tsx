import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { EntryForm } from "@/Components/Competitions/EntryForm";
import Alert from "@/Components/Alert";
import ButtonLink from "@/Components/ButtonLink";
import FlashAlert from "@/Components/FlashAlert";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
        require_times: boolean;
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
    sessions: object[];
    paid: boolean;
};

const EditMemberEntry: Layout<Props> = (props: Props) => {
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
                        name: "Select Events",
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
                    title={`Manage ${props.entrant.first_name}'s entry`}
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
                action={route("competitions.enter.edit_entry", {
                    competition: props.competition.id,
                    member: props.entrant.id,
                })}
                readOnly={props.paid}
            />
        </>
    );
};

EditMemberEntry.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default EditMemberEntry;
