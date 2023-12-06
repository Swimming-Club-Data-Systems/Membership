import * as yup from "yup";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Card from "@/Components/Card";
import Checkbox from "@/Components/Form/Checkbox";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import React, { ReactNode } from "react";
import { useFormikContext } from "formik";
import Alert from "@/Components/Alert";
import Select from "@/Components/Form/Select";
import { AnySchema } from "yup";

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
            <div>
                <DateTimeInput
                    name="dd_club_bills_date"
                    label="Club membership billing date"
                />

                <DateTimeInput
                    name="dd_ngb_bills_date"
                    label="Governing body membership billing date"
                    mb="mb-0"
                />
            </div>
        );
    }

    return null;
};

export const RenewalForm = (props: RenewalFormProps) => {
    const rules: { [key: string]: AnySchema } = {
        start_date: yup.date().required("A start date is required."),
        // .min(date, "Start date must be in the future."),
        end_date: yup
            .date()
            .required("An end date is required.")
            .min(
                yup.ref("start_date"),
                "End date must be later than the start date."
            ),
        dd_ngb_bills_date: yup.date().when("use_custom_billing_dates", {
            is: true,
            then: (schema) => schema.required("A date is required."),
            otherwise: (schema) => schema.notRequired(),
        }),
        dd_club_bills_date: yup.date().when("use_custom_billing_dates", {
            is: true,
            then: (schema) => schema.required("A date is required."),
            otherwise: (schema) => schema.notRequired(),
        }),
    };

    if (props.mode === "create") {
        rules.ngb_year = yup.string().when("club_year", {
            is: "N/A",
            then: (schema) =>
                schema
                    .required(
                        "Either an NGB Membership Year or a Club Membership Year is required."
                    )
                    .test(
                        "is-not-na",
                        "Either an NGB Membership Year or a Club Membership Year is required.",
                        (value) => value !== "N/A"
                    ),
        });
        rules.credit_debit = yup.boolean();
        rules.direct_debit = yup.boolean();
    }

    return (
        <Form
            validationSchema={yup.object().shape(rules)}
            initialValues={{
                start_date: null,
                end_date: null,
                dd_ngb_bills_date: null,
                dd_club_bills_date: null,
                use_custom_billing_dates: false,
            }}
            action={props.action}
            method={props.method}
            submitTitle="Save"
            hideDefaultButtons
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
                    <div>
                        <DateTimeInput
                            name="start_date"
                            label="Renewal period start date"
                        />
                        <DateTimeInput
                            mb="mb-0"
                            name="end_date"
                            label="Renewal period end date"
                        />
                    </div>
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
                    </div>

                    <Checkbox
                        name="use_custom_billing_dates"
                        label="Use custom billing dates"
                    />

                    <Dates />
                </Card>

                {props.mode === "create" && (
                    <Card title="Payment methods">
                        <Checkbox
                            name="credit_debit"
                            label="Credit/debit card"
                            help="Includes Apple Pay and Google Pay"
                        />

                        <Checkbox name="direct_debit" label="Direct Debit" />

                        <div className="prose prose-sm">
                            <p>
                                Payment types will only be available to users if
                                they also meet the criteria for that type. e.g.
                                to pay by Direct Debit, it must be enabled for
                                their renewal session, your club must have
                                Direct Debit payments enabled and the user must
                                have a Direct Debit mandate set up.
                            </p>

                            <p>
                                You can edit payment options for individual
                                users later - for example if your preferred and
                                only enabled payment method for a renewal is
                                Direct Debit but have a member for whom this is
                                inappropriate, you can enable the card payment
                                option just for them.
                            </p>
                        </div>
                    </Card>
                )}
                <Card title="Save changes" footer={<SubmissionButtons />}>
                    <Alert variant="warning" title="Warning">
                        Some settings can only be changed before the start date
                        of this renewal. After the start, you must edit an
                        individual onboarding session instead.
                    </Alert>
                </Card>
            </div>
        </Form>
    );
};
