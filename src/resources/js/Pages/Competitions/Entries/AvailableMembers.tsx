import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import { formatDate } from "@/Utils/date-utils";
import MainLayout from "@/Layouts/MainLayout";
import { RenderServerErrors } from "@/Components/Form/Form";
import FlashAlert from "@/Components/FlashAlert";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";

interface EntryProps {
    id: number;
    first_name: string;
    last_name: string;
    date_of_birth: string;
    sex: string;
    age: number;
    age_on_day: number;
}

type GuestEntryListProps = {
    competition: {
        name: string;
        id: number;
    };
    members: LaravelPaginatorProps<EntryProps>;
};

const EntryRenderer = (props: EntryProps): ReactNode => {
    return (
        <>
            <div>
                <FlashAlert bag={props.id} className="mb-4" />
                <RenderServerErrors />
            </div>
            <div className="grid grid-cols-12 gap-4">
                <div className="col-span-full">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center min-w-0">
                            <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                                <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                                    {props.first_name} {props.last_name}
                                </div>
                                <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                                    {formatDate(props.date_of_birth)} (Age{" "}
                                    {props.age_on_day} on day)
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-1">
                            {/*{props.approved && (*/}
                            {/*    <Badge colour="green">Approved</Badge>*/}
                            {/*)}*/}
                            {/*{props.paid && <Badge colour="yellow">Paid</Badge>}*/}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

const AvailableMembers = (props: GuestEntryListProps) => {
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
                        name: "Members available",
                        route: "competitions.members-available",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Members available for this competition"
                    subtitle={props.competition.name}
                ></MainHeader>

                <Collection
                    {...props.members}
                    route="competitions.enter.edit_entry"
                    routeParams={[props.competition.id]}
                    itemRenderer={(item) => <EntryRenderer {...item} />}
                />
            </Container>
        </>
    );
};

AvailableMembers.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default AvailableMembers;
