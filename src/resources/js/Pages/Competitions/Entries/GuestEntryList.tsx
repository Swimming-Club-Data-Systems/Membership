import React, { JSX, ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import ButtonLink from "@/Components/ButtonLink";
import ActionPanel from "@/Components/ActionPanel";
import Link from "@/Components/Link";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import Card from "@/Components/Card";
import { DefinitionList } from "@/Components/DefinitionList";
import Button from "@/Components/Button";
import BasicList from "@/Components/BasicList";
import MainLayout from "@/Layouts/MainLayout";
import GuestEntryShow from "@/Pages/Competitions/Entries/GuestEntryShow";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import Badge from "@/Components/Badge";

interface EntryProps {
    competition_guest_entrant: {
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
    };
}

interface Entries extends LaravelPaginatorProps {
    data: EntryProps[];
}

type GuestEntryListProps = {
    competition: {
        name: string;
        id: number;
    };
};

const EntryRenderer = (props: EntryProps): ReactNode => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.competition_guest_entrant.first_name}{" "}
                            {props.competition_guest_entrant.last_name}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {formatDate(
                                props.competition_guest_entrant.date_of_birth
                            )}
                        </div>
                    </div>
                </div>
                <Badge colour="indigo">BLAH</Badge>
            </div>
        </>
    );
};

const GuestEntryList = (props: GuestEntryListProps) => {
    return (
        <>
            <Head
                title={props.competition.name}
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
                        name: "Guest Entries",
                        route: "competitions.guest_entries.index",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Guest Entries"
                    subtitle={props.competition.name}
                ></MainHeader>

                <Collection
                    {...props.entries}
                    route="competitions.guest_entries.show"
                    routeParams={[props.competition.id]}
                    itemRenderer={EntryRenderer}
                />
            </Container>
        </>
    );
};

GuestEntryList.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default GuestEntryList;
