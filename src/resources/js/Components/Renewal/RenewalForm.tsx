import * as yup from "yup";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Card from "@/Components/Card";
import Checkbox from "@/Components/Form/Checkbox";
import Form from "@/Components/Form/Form";
import React, { ReactNode } from "react";
import { useFormikContext } from "formik";
import Alert from "@/Components/Alert";
import Select from "@/Components/Form/Select";

type StageField = {
    id: string;
    name: string;
    locked: boolean;
};

type RenewalFormProps = {
    mode: "create" | "edit";
    started: boolean;
    action: string;
    method: string;
    user_fields: StageField[];
    member_fields: StageField[];
    membership_years?: {
        value: string;
        name: ReactNode;
    }[];
};

const RequiredStages = () => (
    <Alert
        title="This renewal period has started, therefore you can not edit the required stages"
        variant="warning"
        className="mb-6"
    >
        <p>
            You can edit the required stages for an individual user by finding
            their onboarding session.
        </p>
    </Alert>
);

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

export const RenewalForm = (props: RenewalFormProps) => {
    return (
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
                dd_ngb_bills_date: yup.date().when("use_custom_billing_dates", {
                    is: true,
                    then: (schema) => schema.required("A date is required"),
                }),
                dd_club_bills_date: yup
                    .date()
                    .when("use_custom_billing_dates", {
                        is: true,
                        then: (schema) => schema.required("A date is required"),
                    }),
            })}
            initialValues={{}}
            action={props.action}
            method={props.method}
            submitTitle="Save"
        >
            <div className="grid gap-6">
                {props.mode === "create" && (
                    <Card title="Membership years">
                        <Select
                            name="ngb_year"
                            label="Swim England Membership Year"
                            items={props.membership_years}
                        />

                        <Select
                            name="club_year"
                            label="Club Membership Year"
                            items={props.membership_years}
                        />
                    </Card>
                )}

                <Card title="Period dates">
                    <DateTimeInput
                        mb="mb-0"
                        name="start_date"
                        label="Renewal period start date"
                    />
                    <DateTimeInput
                        mb="mb-0"
                        name="end_date"
                        label="Renewal period end date"
                    />
                </Card>

                <Card title="Required stages">
                    {props.started && <RequiredStages />}

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

                <Card title="Member information stage">
                    {props.started && <RequiredStages />}

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
                        Photography consents will only be asked from members who
                        are aged under 18 when renewal opens.{" "}
                    </p>
                </Card>

                <Card title="Custom Direct Debit billing dates">
                    <div className="prose prose-sm">
                        <p>
                            For clubs supporting payment by Direct Debit, you
                            can select a custom date on which to bill the Swim
                            England and Club Membership fee components.
                            Selecting a custom date only applies when members
                            choose to pay renewal fees by Direct Debit - if they
                            pay by card they will pay their entire renewal fee
                            in one go.
                        </p>

                        <p>
                            Members will be charged on their first billing day
                            on or after your selected bill date. Please note
                            that fees will not be automatically added to
                            accounts if users do not complete renewal.
                        </p>

                        <p>
                            To use custom bill dates, you must tick the Use
                            custom bill dates checkbox.
                        </p>

                        {props.started && (
                            <p>
                                <strong>
                                    Changes made here will not apply to any
                                    member who has already completed renewal.
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

                {props.mode === "create" && (
                    <Card title="Payment methods">
                        <Checkbox
                            name="credit_debit"
                            label="Credit/debit card"
                            help="Includes Apple Pay and Google Pay"
                        />

                        <Checkbox name="direct_debit" label="Direct Debit" />
                    </Card>
                )}
            </div>
        </Form>
    );
};
