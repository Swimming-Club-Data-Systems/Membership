import React, { useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, { SubmitButton } from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import Checkbox from "@/Components/Form/Checkbox";
import BasicList from "@/Components/BasicList";
import { useFormikContext } from "formik";
import { formatCurrency } from "@/Utils/Payments/MoneyHelpers";
import BigNumber from "bignumber.js";
import ArrayErrorMessage from "@/Components/Form/ArrayErrorMessage";
import { Elements } from "@stripe/react-stripe-js";
import { StripeElementsOptions } from "@stripe/stripe-js";
import { appearance } from "@/Utils/Stripe/Appearance";
import { useLoadStripe } from "@/Components/Payments/useLoadStripe";
import { ExpressCheckout } from "@/Components/Payments/Checkout/ExpressCheckout";

export type Props = {
    stripe_account: string;
    stripe_publishable_key: string;
    user_not_direct_debit: boolean;
    tenant_not_direct_debit: boolean;
    entries: {
        id: string;
        member: {
            id: number;
            name: string;
        };
        competition: {
            id: number;
            name: string;
        };
        amount: number;
        amount_currency: string;
    }[];
    payment_method_types: string[];
};

const Total = ({ setTotal }) => {
    const {
        values,
    }: {
        values: {
            entries: {
                paying: boolean;
                amount: number;
            }[];
        };
    } = useFormikContext();
    const total = values.entries.reduce((currentTotal, currentValue) => {
        return currentValue.paying
            ? currentValue.amount + currentTotal
            : currentTotal;
    }, 0);

    setTotal(total);

    return null;
};

const PaymentButtons = ({
    stripe,
    options,
}: {
    stripe: any;
    options: StripeElementsOptions;
}) => {
    const values = useFormikContext();

    return (
        <Elements stripe={stripe} options={options}>
            <ExpressCheckout
                createIntentRoute={route("competitions.pay")}
                createIntentPostData={values}
            />

            <div className="d-grid">
                <SubmitButton className="py-[11px] w-full" />
            </div>
        </Elements>
    );
};

const SelectEntriesToPayFor: Layout<Props> = (props: Props) => {
    const [total, setTotal] = useState<number>(0);
    const stripe = useLoadStripe(
        props.stripe_account,
        props.stripe_publishable_key,
    );

    const notDirectDebit =
        props.tenant_not_direct_debit || props.user_not_direct_debit;

    const options: StripeElementsOptions = {
        mode: "payment",
        amount: total,
        currency: "gbp",
        appearance: appearance,
        payment_method_types: props.payment_method_types,
    };

    return (
        <>
            <Head
                title="Pay for entries"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: "Pay for entries",
                        route: "competitions.new",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Pay for entries"}
                    subtitle={`Select entries to pay for`}
                ></MainHeader>

                <Form
                    hideDefaultButtons
                    validationSchema={yup.object().shape({
                        entries: yup
                            .array()
                            .of(
                                yup.object().shape({
                                    paying: yup.boolean(),
                                    amount: yup.number(),
                                }),
                            )
                            .test(
                                "sum-of-totals",
                                "You must select enough entries to pay more than £0.00.",
                                (entries) => {
                                    const total = entries.reduce(
                                        (currentTotal, currentValue) => {
                                            return currentValue.paying
                                                ? currentValue.amount +
                                                      currentTotal
                                                : currentTotal;
                                        },
                                        0,
                                    );
                                    return total > 0;
                                },
                            ),
                    })}
                    initialValues={{}}
                    submitTitle={`Pay ${formatCurrency(
                        new BigNumber(total),
                    )} now`}
                    action={route("competitions.pay")}
                    method="post"
                    hideClear
                >
                    <Total setTotal={setTotal} />
                    <div className="grid gap-6">
                        <Card title="Select entries">
                            <div className="prose prose-sm">
                                <p>
                                    {notDirectDebit && (
                                        <>
                                            You must pay for your entries by
                                            card or any other accepted method.
                                        </>
                                    )}
                                    {!notDirectDebit && (
                                        <>
                                            If you don't make a payment by card,
                                            you'll be automatically charged for
                                            gala entries as part of your next
                                            direct debit payment after the gala
                                            coordinator submits the entries to
                                            the host club.
                                        </>
                                    )}
                                </p>

                                <p className="">
                                    Select which galas you would like to pay
                                    for.{" "}
                                    <strong>
                                        You can pay for all, some or just one of
                                        your gala entries in a single payment.
                                    </strong>
                                </p>
                            </div>

                            <ArrayErrorMessage name="entries" />

                            <BasicList
                                items={props.entries.map((entry, idx) => {
                                    return {
                                        id: entry.id,
                                        content: (
                                            <React.Fragment key={entry.id}>
                                                <div className="text-sm mb-3">
                                                    <div>
                                                        {entry.member.name},{" "}
                                                        {entry.competition.name}
                                                    </div>
                                                    <div>
                                                        {entry.amount_currency}
                                                    </div>
                                                </div>
                                                <Checkbox
                                                    name={`entries[${idx}].paying`}
                                                    label="Pay for this entry"
                                                />
                                            </React.Fragment>
                                        ),
                                    };
                                })}
                            />

                            {total > 0 && (
                                <PaymentButtons
                                    stripe={stripe}
                                    options={options}
                                />
                            )}
                        </Card>
                    </div>
                </Form>
            </Container>
        </>
    );
};

SelectEntriesToPayFor.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default SelectEntriesToPayFor;
