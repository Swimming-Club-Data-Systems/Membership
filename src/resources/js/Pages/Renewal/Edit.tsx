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

type StageField = {
    id: string;
    name: string;
    locked: boolean;
};

interface Props extends RenewalProps {
    user_fields: StageField[];
    member_fields: StageField[];
}

const Dates = () => {
    const {
        values: { use_custom_billing_dates },
    } = useFormikContext<{ use_custom_billing_dates: boolean }>();

    if (use_custom_billing_dates) {
        return (
            <>
                <DateTimeInput
                    name="dd_club_bills_date"
                    label="Club membership billing date"
                />

                <DateTimeInput
                    name="dd_ngb_bills_date"
                    label="Governing body membership billing date"
                />
            </>
        );
    }

    return null;
};

const Show: Layout<Props> = (props: Props) => {
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
                            start_date: yup.date(),
                            // .min(date, "Start date must be in the future."),
                            end_date: yup
                                .date()
                                .min(
                                    yup.ref("start_date"),
                                    "End date must be later than the start date."
                                ),
                            dd_ngb_bills_date: yup
                                .date()
                                .when("use_custom_billing_dates", {
                                    is: true,
                                    then: (schema) =>
                                        schema.required("A date is required"),
                                }),
                            dd_club_bills_date: yup
                                .date()
                                .when("use_custom_billing_dates", {
                                    is: true,
                                    then: (schema) =>
                                        schema.required("A date is required"),
                                }),
                        })}
                        initialValues={{}}
                        action={route("renewals.update", props.id)}
                        method="put"
                        submitTitle="Save"
                    >
                        <DateTimeInput
                            name="start_date"
                            label="Renewal period start date"
                        />
                        <DateTimeInput
                            name="end_date"
                            label="Renewal period end date"
                        />

                        {props.started && (
                            <Alert
                                title="This renewal period has started,
                                        therefore you can not edit the required
                                        stages"
                                variant="warning"
                                className="mb-6"
                            >
                                <p>
                                    You can edit the required stages for an
                                    individual user by finding their onboarding
                                    session.
                                </p>
                            </Alert>
                        )}

                        <Card title="Requried stages" className="mb-6">
                            {props.user_fields.map((field) => {
                                return (
                                    <Checkbox
                                        key={field.id}
                                        name={field.id}
                                        label={field.name}
                                        disabled={props.started || field.locked}
                                    />
                                );
                            })}
                        </Card>

                        <Card title="Member information stage" className="mb-6">
                            {props.member_fields.map((field) => {
                                return (
                                    <Checkbox
                                        key={field.id}
                                        name={field.id}
                                        label={field.name}
                                        disabled={props.started || field.locked}
                                    />
                                );
                            })}

                            <p className="text-sm">
                                Photography consents will only be asked from
                                members who are aged under 18 when renewal
                                opens.{" "}
                            </p>
                        </Card>

                        <Card title="Custom Direct Debit billing dates">
                            <div className="prose prose-sm">
                                <p>
                                    For clubs supporting payment by Direct
                                    Debit, you can select a custom date on which
                                    to bill the Swim England and Club Membership
                                    fee components. Selecting a custom date only
                                    applies when members choose to pay renewal
                                    fees by Direct Debit - if they pay by card
                                    they will pay their entire renewal fee in
                                    one go.
                                </p>

                                <p>
                                    Members will be charged on their first
                                    billing day on or after your selected bill
                                    date. Please note that fees will not be
                                    automatically added to accounts if users do
                                    not complete renewal.
                                </p>

                                <p>
                                    To use custom bill dates, you must tick the
                                    Use custom bill dates checkbox.
                                </p>

                                {props.started && (
                                    <p>
                                        <strong>
                                            Changes made here will not apply to
                                            any member who has already completed
                                            renewal.
                                        </strong>
                                    </p>
                                )}

                                <Checkbox
                                    name="use_custom_billing_dates"
                                    label="Use custom billing dates"
                                />

                                <Dates />
                            </div>
                        </Card>
                    </Form>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
