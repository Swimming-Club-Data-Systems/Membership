import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/react";
import Layout from "./Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Checkbox from "@/Components/Form/Checkbox";
import Select from "@/Components/Form/Select";
import { useField, useFormikContext } from "formik";
import DateTimeInput from "@/Components/Form/DateTimeInput";

// const WrappedDate = (props) => {
//     const { setFieldValue } = useFormikContext();
//     const [field] = useField(props);
//
//     return (
//         <DateTimeInput
//             id={props.name}
//             {...props}
//             {...field}
//             onChange={(ev) => {
//                 console.log(ev);
//                 setFieldValue(props.name, ev.target.value.formattedValue);
//             }}
//         />
//     );
// };

const Payments = (props) => {
    const mustBeBool = yup.boolean().oneOf([true, false], "Must be yes or no");
    const dayOptions = [];
    for (let i = 1; i < 29; i++) {
        dayOptions.push({
            key: i,
            name: `Day ${i} of each month`,
        });
    }

    return (
        <>
            <Head title="Payments" />

            {/*<Container noMargin className="py-12"></Container>*/}

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Form
                    initialValues={{
                        date: "2022-10-01",
                        use_payments_v2: false,
                        enable_automated_billing_system: false,
                        hide_squad_fees_from_move_emails: false,
                        squad_fee_calculation_date: 1,
                        fee_calculation_date: 1,
                        billing_date: 1,
                        allow_user_billing_date_override_by_admin: false,
                        allow_user_billing_date_override_by_user: false,
                        allow_account_top_up: false,
                        membership_payment_methods: {
                            account: false,
                            card: false,
                            bacs_debit: false,
                        },
                        gala_entry_payment_methods: {
                            account: false,
                            card: false,
                            bacs_debit: false,
                        },
                        balance_payment_methods: {
                            card: false,
                            bacs_debit: false,
                        },
                    }}
                    validationSchema={yup.object().shape({
                        date: yup.date().required(),
                        use_payments_v2: mustBeBool,
                        enable_automated_billing_system: mustBeBool,
                        hide_squad_fees_from_move_emails: mustBeBool,
                        squad_fee_calculation_day: yup
                            .number()
                            .min(1, "Must be at least one")
                            .max(28, "Must not be more than 28")
                            .integer("Must be a whole number"),
                        fee_calculation_day: yup
                            .number()
                            .min(1, "Must be at least one")
                            .max(28, "Must not be more than 28")
                            .integer("Must be a whole number"),
                        billing_day: yup
                            .number()
                            .min(1, "Must be at least one")
                            .max(28, "Must not be more than 28")
                            .integer("Must be a whole number"),
                        allow_user_billing_date_override_by_admin: mustBeBool,
                        allow_user_billing_date_override_by_user: mustBeBool,
                        allow_account_top_up: mustBeBool,
                        membership_payment_methods: yup.object().shape({
                            account: mustBeBool,
                            card: mustBeBool,
                            bacs_debit: mustBeBool,
                        }),
                        gala_entry_payment_methods: yup.object().shape({
                            account: mustBeBool,
                            card: mustBeBool,
                            bacs_debit: mustBeBool,
                        }),
                        balance_payment_methods: yup.object().shape({
                            card: mustBeBool,
                            bacs_debit: mustBeBool,
                        }),
                    })}
                    action={route("settings.payments")}
                    submitTitle="Save changes"
                    hideClear
                    hideDefaultButtons
                    hideErrors
                    removeDefaultInputMargin
                    method="put"
                >
                    <div className="space-y-6">
                        <RenderServerErrors />
                        <FlashAlert className="mb-4" />

                        <Card>
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900">
                                    Basic settings
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Generic payments settings.
                                </p>
                            </div>

                            <div className="grid grid-cols-6 gap-6">
                                <div className="col-span-6 sm:col-span-4">
                                    {/*<WrappedDate name="date" label="Date" />*/}
                                </div>
                                <div className="col-span-6">
                                    <Checkbox
                                        name="use_payments_v2"
                                        label="Use the new payments system"
                                    />
                                    <Checkbox
                                        name="enable_automated_billing_system"
                                        label="Enable the automated billing system"
                                        help="This is the system that bills users by Direct Debit. Turn this off if you don't utilise Direct Debit or Payments on Account."
                                    />
                                    <Checkbox
                                        name="hide_squad_fees_from_move_emails"
                                        label="Hide squad fees from squad move notification emails"
                                    />
                                </div>
                            </div>
                        </Card>

                        <Card>
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900">
                                    New payments system options
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Settings for Payments V2.
                                </p>
                            </div>

                            <div className="grid grid-cols-6 gap-6">
                                <div className="col-span-6 sm:col-span-4">
                                    {/*<Select*/}
                                    {/*    name="squad_fee_calculation_date"*/}
                                    {/*    label="Squad fee calculation day"*/}
                                    {/*    options={dayOptions}*/}
                                    {/*    help="The day of the month on which we will calculate squad and extra fees and post them to user accounts."*/}
                                    {/*/>*/}
                                </div>
                                <div className="col-span-6 sm:col-span-4">
                                    {/*<Select*/}
                                    {/*    name="fee_calculation_date"*/}
                                    {/*    label="Fee calculation day"*/}
                                    {/*    options={dayOptions}*/}
                                    {/*    help="The day of the month on which we will calculate amounts due to clear an account balance and generate statements."*/}
                                    {/*/>*/}
                                </div>
                                <div className="col-span-6 sm:col-span-4">
                                    {/*<Select*/}
                                    {/*    name="billing_date"*/}
                                    {/*    label="Billing day"*/}
                                    {/*    options={dayOptions}*/}
                                    {/*    help="The day of the month on which we will request Direct Debit payments for each user's most recent statement"*/}
                                    {/*/>*/}
                                </div>
                                <div className="col-span-6">
                                    <Checkbox
                                        name="allow_user_billing_date_override_by_admin"
                                        label="Allow user billing day override by administrators"
                                        help="Allow an administrator to change the day on which we request a Direct Debit payment for a specific user's most recent statement. If you turn off this setting any customised billing dates will still apply."
                                    />
                                    <Checkbox
                                        name="allow_user_billing_date_override_by_user"
                                        label="Allow user billing day override by users"
                                        help="Allow a user to change the day on which we request a Direct Debit payment for their most recent statement. Users may only set this once. If they need to change this, they must ask an administrator to do it for them.  If you turn off this setting any customised billing dates will still apply."
                                    />
                                </div>
                                <div className="col-span-6">
                                    <Checkbox
                                        name="allow_account_top_up"
                                        label="Allow users to top up their account balance"
                                        help="Account balance top ups can only be paid by card and is separate from functionality to make payments to reduce an account balance"
                                    />
                                </div>
                            </div>
                        </Card>

                        <Card>
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900">
                                    Payment methods
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Choose available payment methods for
                                    Payments V2.
                                </p>
                            </div>

                            <div className="grid grid-cols-6 gap-6">
                                <div className="col-span-6">
                                    <Checkbox
                                        name="membership_payment_methods.account"
                                        label="Allow payment on account for annual membership fees"
                                        help="Payment on account posts transactions to the user's journal and account balance to be paid as part of their next bill."
                                    />
                                    <Checkbox
                                        name="membership_payment_methods.card"
                                        label="Allow payment by card for annual membership fees"
                                    />
                                    <Checkbox
                                        name="membership_payment_methods.bacs_debit"
                                        label="Allow payment by BACS Direct Debit for annual membership fees"
                                    />
                                </div>

                                <div className="col-span-6">
                                    <Checkbox
                                        name="gala_entry_payment_methods.account"
                                        label="Allow payment on account for gala entries (club members)"
                                        help="Payment on account posts transactions to the user's journal and account balance to be paid as part of their next bill."
                                    />
                                    <Checkbox
                                        name="gala_entry_payment_methods.card"
                                        label="Allow payment by card for gala entries (club members)"
                                    />
                                    <Checkbox
                                        name="gala_entry_payment_methods.bacs_debit"
                                        label="Allow payment by BACS Direct Debit for gala entries (club members)"
                                    />
                                </div>

                                <div className="col-span-6">
                                    <Checkbox
                                        name="balance_payment_methods.card"
                                        label="Allow additional payments by card to reduce a user's account balance"
                                    />
                                    <Checkbox
                                        name="balance_payment_methods.bacs_debit"
                                        label="Allow additional payments by BACS Direct Debit to reduce a user's account balance"
                                    />
                                </div>
                            </div>
                        </Card>

                        <SubmissionButtons />
                    </div>
                </Form>
            </div>
        </>
    );
};

Payments.layout = (page) => (
    <MainLayout
        title="Payment Settings"
        subtitle="Manage Stripe, billing and payment options"
        breadcrumbs={[
            {
                name: "Settings",
                route: "settings.index",
            },
            {
                name: "Payment Settings",
                route: "settings.payments",
            },
        ]}
    >
        <Layout>{page}</Layout>
    </MainLayout>
);

export default Payments;
