import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { formatDate } from "@/Utils/date-utils";
import ButtonLink from "@/Components/ButtonLink";
import { RenewalProps } from "@/Pages/Renewal/Index";
import Card from "@/Components/Card";
import Link from "@/Components/Link";

interface Props extends RenewalProps {
    can_edit: boolean;
}

const Show: Layout<Props> = (props: Props) => {
    const pageName = `Renewal Period ${formatDate(props.start)} - ${formatDate(
        props.end
    )}`;

    let subtitle = "For the ";
    if (props.club_year) {
        subtitle += `${formatDate(props.club_year.StartDate)} - ${formatDate(
            props.club_year.EndDate
        )} club year`;
        if (props.ngb_year) {
            subtitle += ` and the `;
        }
    }
    if (props.ngb_year) {
        subtitle += `${formatDate(props.ngb_year.StartDate)} - ${formatDate(
            props.ngb_year.EndDate
        )} NGB year`;
    }

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

            <Container>
                <MainHeader
                    title={pageName}
                    subtitle={subtitle}
                    buttons={
                        props.can_edit && (
                            <ButtonLink href={route("renewals.edit", props.id)}>
                                Edit
                            </ButtonLink>
                        )
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
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
