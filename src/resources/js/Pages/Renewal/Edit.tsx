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

const Show: Layout<RenewalProps> = (props: RenewalProps) => {
    const pageName = `Edit Renewal Period ${formatDate(
        props.start
    )} - ${formatDate(props.end)}`;
    const date = new Date();
    date.setHours(0, 0, 0, 0);
    console.log(date);

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
                <MainHeader
                    title={pageName}
                    subtitle={`For the ${formatDate(
                        props.club_year.StartDate
                    )} - ${formatDate(
                        props.club_year.EndDate
                    )} club year and ${formatDate(
                        props.ngb_year.StartDate
                    )} - ${formatDate(props.ngb_year.EndDate)} NGB year`}
                ></MainHeader>

                <div className="grid gap-6">
                    <Form
                        validationSchema={yup.object().shape({
                            start: yup
                                .date()
                                .min(date, "Start date must be in the future."),
                            end: yup
                                .date()
                                .min(
                                    yup.ref("start"),
                                    "End date must be later than the start date."
                                ),
                        })}
                        initialValues={{}}
                        action={route("renewals.update", props.id)}
                        method="put"
                        submitTitle="Save"
                    >
                        <DateTimeInput
                            name="start"
                            label="Renewal period start date"
                        />
                        <DateTimeInput
                            name="end"
                            label="Renewal period end date"
                        />

                        <p>Required stages</p>

                        <Checkbox name="x" label="Set your account password" />
                        <Checkbox name="x" label="Tell us your address" />
                        <Checkbox
                            name="x"
                            label="Tell us your communications options"
                        />
                        <Checkbox
                            name="x"
                            label="Tell us your emergency contact details"
                        />
                        <Checkbox
                            name="x"
                            label="Complete member information"
                        />
                        <Checkbox
                            name="x"
                            label="Agree to the parent/guardian Code of Conduct"
                        />
                        <Checkbox name="x" label="Data Privacy Agreement" />
                        <Checkbox
                            name="x"
                            label="Agree to the terms and conditions of club membership"
                        />
                        <Checkbox
                            name="x"
                            label="Set up a Direct Debit Instruction"
                        />
                        <Checkbox name="x" label="Pay your registration fees" />

                        <p>Member information stage includes the following; </p>

                        <Checkbox name="x" label="Medical form" />
                        <Checkbox name="x" label="Photography consent" />
                        <Checkbox name="x" label="Code of conduct" />

                        <p>
                            Photography consents will only be asked from members
                            who are aged under 18 when renewal opens.{" "}
                        </p>
                    </Form>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
