import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import { RenewalProps } from "@/Pages/Renewal/Index";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import Alert from "@/Components/Alert";
import Card from "@/Components/Card";
import { useFormikContext } from "formik";
import { RenewalForm } from "@/Components/Renewal/RenewalForm";

type StageField = {
    id: string;
    name: string;
    locked: boolean;
};

interface Props extends RenewalProps {
    user_fields: StageField[];
    member_fields: StageField[];
}

const Show: Layout<Props> = (props: Props) => {
    const pageName = `Edit Renewal Period ${formatDate(
        props.start
    )} - ${formatDate(props.end)}`;
    const date = new Date();
    date.setHours(0, 0, 0, 0);

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
                    {
                        name: "Edit",
                        route: "renewals.edit",
                        routeParams: props.id,
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader title={pageName} subtitle={subtitle}></MainHeader>

                <div className="grid gap-6">
                    <RenewalForm
                        mode="edit"
                        started={props.started}
                        action={route("renewals.update", props.id)}
                        method="put"
                        user_fields={props.user_fields}
                        member_fields={props.member_fields}
                    />
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
