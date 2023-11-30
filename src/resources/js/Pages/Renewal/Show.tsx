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
import { RenewalProps } from "@/Pages/Renewal/Index";
import Card from "@/Components/Card";
import Link from "@/Components/Link";

const Show: Layout<RenewalProps> = (props: RenewalProps) => {
    const pageName = `Renewal Period ${formatDate(props.start)} - ${formatDate(
        props.end
    )}`;

    return (
        <>
            <Head
                title={pageName}
                breadcrumbs={[
                    { name: "Renewal Periods", route: "renewals.index" },
                    {
                        name: `${formatDate(props.start)} - ${formatDate(
                            props.end
                        )}`,
                        route: "renewals.show",
                        routeParams: props.id,
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={pageName}
                    subtitle={`For the ${formatDate(
                        props.club_year.StartDate
                    )} - ${formatDate(
                        props.club_year.EndDate
                    )} club year and ${formatDate(
                        props.ngb_year.StartDate
                    )} - ${formatDate(props.ngb_year.EndDate)} NGB year`}
                    buttons={
                        <ButtonLink href={route("renewals.edit", props.id)}>
                            Edit
                        </ButtonLink>
                    }
                ></MainHeader>

                <div className="grid gap-6">
                    <Card title="View member renewal status">
                        <ul className="text-sm">
                            <li>
                                <Link
                                    href={`/v1/memberships/renewal/${props.id}/renewal-member-list`}
                                    external
                                >
                                    Classic report
                                </Link>
                            </li>
                        </ul>
                    </Card>

                    <Card title="View associated onboarding sessions">
                        <ul className="text-sm">
                            <li>
                                <Link
                                    href={`/v1/onboarding/all?renewal=${props.id}`}
                                    external
                                >
                                    All
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={`/v1/onboarding/all?renewal=${props.id}&type=not_ready`}
                                    external
                                >
                                    Not ready
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={`/v1/onboarding/all?renewal=${props.id}&type=pending`}
                                    external
                                >
                                    Pending
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={`/v1/onboarding/all?renewal=${props.id}&type=in_progress`}
                                    external
                                >
                                    In progress
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={`/v1/onboarding/all?renewal=${props.id}&type=complete`}
                                    external
                                >
                                    Complete
                                </Link>
                            </li>
                        </ul>
                    </Card>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
