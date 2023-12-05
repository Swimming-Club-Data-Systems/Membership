import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import ButtonLink from "@/Components/ButtonLink";
import Badge from "@/Components/Badge";
import { courseLength } from "@/Utils/Competitions/CourseLength";

type MembershipYearProps = {
    id: string;
    StartDate: string;
    EndDate: string;
};

export interface RenewalProps {
    id: string;
    start: string;
    end: string;
    club_year: MembershipYearProps;
    ngb_year: MembershipYearProps;
    started: boolean;
}

interface Renewals extends LaravelPaginatorProps {
    data: RenewalProps[];
}

export type Props = {
    renewals: Renewals;
    can_create: boolean;
};

const RenewalRenderer = (props: RenewalProps): ReactNode => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {formatDate(props.start)} - {formatDate(props.end)}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.club_year && (
                                <p>
                                    For the club membership year{" "}
                                    {formatDate(props.club_year.StartDate)} -{" "}
                                    {formatDate(props.club_year.EndDate)}
                                </p>
                            )}
                            {props.ngb_year && (
                                <p>
                                    For the Swim England membership year{" "}
                                    {formatDate(props.ngb_year.StartDate)} -{" "}
                                    {formatDate(props.ngb_year.EndDate)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
                <Badge colour="indigo">OPEN???</Badge>
            </div>
        </>
    );
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Renewal Periods"
                breadcrumbs={[
                    { name: "Renewal Periods", route: "renewals.index" },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Renewal Periods"}
                    subtitle={`All renewal periods`}
                    buttons={
                        props.can_create && (
                            <ButtonLink href={route("renewals.new")}>
                                New
                            </ButtonLink>
                        )
                    }
                ></MainHeader>

                <Collection
                    searchable
                    {...props.renewals}
                    route="renewals.show"
                    itemRenderer={RenewalRenderer}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
