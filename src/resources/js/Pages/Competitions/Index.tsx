import React, { ReactNode, useEffect, useRef } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import { formatDateTime } from "@/Utils/date-utils";
import ButtonLink from "@/Components/ButtonLink";
import Badge from "@/Components/Badge";

interface CompetitionProps {
    id: number;
    name: string;
    pool_course: string;
    venue: {
        id: number;
        name;
        string;
        formatted_address: string;
    };
    sessions: {
        id: number;
        name: string;
        start_time: string;
        end_time: string;
    }[];
}

interface Competitions extends LaravelPaginatorProps {
    data: CompetitionProps[];
}

export type Props = {
    competitions: Competitions;
    can_create: boolean;
};

const CompetitionRenderer = (props: CompetitionProps): ReactNode => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.name}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.venue.name} - {props.venue.formatted_address}
                        </div>
                        <ul className="text-sm text-gray-700">
                            {props.sessions.map((session) => (
                                <li key={session.id}>
                                    {props.sessions.length > 1 && (
                                        <>{session.name}, </>
                                    )}
                                    {formatDateTime(session.start_time)} -{" "}
                                    {formatDateTime(session.end_time)}
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
                <Badge colour="indigo">{props.pool_course}</Badge>
            </div>
        </>
    );
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Competitions"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Competitions"}
                    subtitle={`All competitions`}
                    buttons={
                        props.can_create && (
                            <ButtonLink href={route("competitions.new")}>
                                New
                            </ButtonLink>
                        )
                    }
                ></MainHeader>

                <Collection
                    searchable
                    {...props.competitions}
                    route="competitions.show"
                    itemRenderer={CompetitionRenderer}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
