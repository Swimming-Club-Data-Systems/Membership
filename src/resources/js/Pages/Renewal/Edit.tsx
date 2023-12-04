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

                        {props.started && (
                            <Alert
                                title="This renewal period has started,
                                        therefore you can not edit the required
                                        stages"
                                variant="warning"
                                className="mb-3"
                            >
                                <p>
                                    You can edit the required stages for an
                                    individual user by finding their onboarding
                                    session.
                                </p>
                            </Alert>
                        )}

                        <Card title="Requried stages" className="mb-3">
                            <Checkbox
                                name="account_details"
                                label="Set your account password"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="address_details"
                                label="Tell us your address"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="communications_options"
                                label="Tell us your communications options"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="emergency_contacts"
                                label="Tell us your emergency contact details"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="member_forms"
                                label="Complete member information"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="parent_conduct"
                                label="Agree to the parent/guardian Code of Conduct"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="data_privacy_agreement"
                                label="Data Privacy Agreement"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="terms_agreement"
                                label="Agree to the terms and conditions of club membership"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="direct_debit_mandate"
                                label="Set up a Direct Debit Instruction"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="fees"
                                label="Pay your registration fees"
                                disabled={props.started}
                            />
                        </Card>

                        <Card title="Member information stage">
                            <Checkbox
                                name="medical_form"
                                label="Medical form"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="photography_consent"
                                label="Photography consent"
                                disabled={props.started}
                            />
                            <Checkbox
                                name="code_of_conduct"
                                label="Code of conduct"
                                disabled={props.started}
                            />

                            <p>
                                Photography consents will only be asked from
                                members who are aged under 18 when renewal
                                opens.{" "}
                            </p>
                        </Card>
                    </Form>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
