import React, { ReactNode } from "react";
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
import BasicList from "@/Components/BasicList";
import { formatDate } from "@/Utils/date-utils";
import { DefinitionList } from "@/Components/DefinitionList";
import Link from "@/Components/Link";
import { EntryForm } from "@/Components/Competitions/EntryForm";
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
    sessions: {}[];
    paid: boolean;
};

type FieldArrayItemsProps = {
    name: string;
    render: (index: number, length: number) => ReactNode;
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
                            be amended. You can amend some of{" "}
                            {props.entrant.first_name}'s personal details if
                            required. For any other changes, you must contact{" "}
                            {props.tenant.name} directly.
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
