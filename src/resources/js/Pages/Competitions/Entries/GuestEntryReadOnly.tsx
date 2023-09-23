import React, { JSX, ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import ButtonLink from "@/Components/ButtonLink";
import ActionPanel from "@/Components/ActionPanel";
import Link from "@/Components/Link";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import Card from "@/Components/Card";
import { DefinitionList } from "@/Components/DefinitionList";
import Button from "@/Components/Button";
import BasicList from "@/Components/BasicList";
import MainLayout from "@/Layouts/MainLayout";
import GuestEntryShow from "@/Pages/Competitions/Entries/GuestEntryShow";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import Badge from "@/Components/Badge";
import PlainCollection from "@/Components/PlainCollection";
import { usePage } from "@inertiajs/react";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Checkbox from "@/Components/Form/Checkbox";
import Alert from "@/Components/Alert";
import FlashAlert from "@/Components/FlashAlert";

interface EntryProps {
    id: string;
    competition: {
        id: number;
        name: string;
        age_at_date: string;
    };
    entrant: {
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
        age: number;
        age_on_day: number;
    };
    header: {
        id: string;
        name: string;
        email: string;
    };
    amount: number;
    amount_refunded: number;
    amount_string: string;
    amount_refunded_string: string;
    approved: boolean;
    locked: boolean;
    paid: boolean;
    processed: boolean;
    entries: {
        id: string;
        entry_time: string;
        event: {
            id: number;
            name: string;
        };
    }[];
    payment?: {
        id: number;
        stripe_id: string;
        stripe_status: string;
        stripe_fee: number;
        amount: number;
        formatted_amount: string;
        payment_method?: {
            id: number;
            stripe_id: string;
            description: string;
            information_line: string;
        };
    };
    created_at: string;
    updated_at: string;
}

const GuestEntryReadOnly = (props: EntryProps) => {
    return (
        <>
            <Head
                title={props.competition.name}
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    {
                        name: "Guest Entries",
                        route: "competitions.guest_entries.index",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    {
                        name: "Entry",
                        route: "competitions.guest_entries.show",
                        routeParams: {
                            competition: props.competition.id,
                            entry: props.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={`${props.entrant.first_name} ${props.entrant.last_name} guest entry`}
                    subtitle={props.competition.name}
                ></MainHeader>

                <Form
                    initialValues={{
                        processed: props.processed,
                        approved: props.approved,
                        locked: props.locked,
                        paid: props.paid,
                    }}
                    validationSchema={yup.object().shape({
                        processed: yup.boolean(),
                        approved: yup.boolean(),
                        locked: yup.boolean(),
                        paid: yup.boolean(),
                    })}
                    submitTitle="Save"
                    hideDefaultButtons
                    method="put"
                    action={route("competitions.guest_entries.show", [
                        props.competition.id,
                        props.id,
                    ])}
                >
                    <div className="grid gap-4 grid-cols-12">
                        <div className="col-start-1 col-span-8">
                            <div className="grid gap-4">
                                <FlashAlert />
                                <RenderServerErrors />

                                <Card
                                    title="Basic details"
                                    footer={<SubmissionButtons />}
                                >
                                    <div className="grid grid-cols-12 gap-4">
                                        <div className="col-span-full">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center min-w-0">
                                                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                                                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                                                            {formatDate(
                                                                props.entrant
                                                                    .date_of_birth
                                                            )}{" "}
                                                            (Age{" "}
                                                            {props.entrant.age})
                                                        </div>
                                                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                                                            {props.entrant.sex}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex gap-1">
                                                    {props.approved && (
                                                        <Badge colour="green">
                                                            Approved
                                                        </Badge>
                                                    )}
                                                    {props.paid && (
                                                        <Badge colour="yellow">
                                                            Paid
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="col-start-1 col-span-6 text-sm text-gray-700">
                                            <ul>
                                                {props.entries.map(
                                                    (event_entry) => {
                                                        return (
                                                            <li
                                                                key={
                                                                    event_entry.id
                                                                }
                                                            >
                                                                {
                                                                    event_entry
                                                                        .event
                                                                        .name
                                                                }{" "}
                                                                {event_entry.entry_time && (
                                                                    <>
                                                                        (
                                                                        {
                                                                            event_entry.entry_time
                                                                        }
                                                                        )
                                                                    </>
                                                                )}
                                                            </li>
                                                        );
                                                    }
                                                )}
                                            </ul>
                                        </div>
                                        <div className="col-start-7 col-span-6 text-sm">
                                            <div>
                                                Amount £{props.amount_string}
                                            </div>
                                            <div>
                                                Amount refunded £
                                                {props.amount_refunded_string}
                                            </div>
                                            <Checkbox
                                                name="approved"
                                                label="Approved"
                                            />
                                            <Checkbox
                                                name="locked"
                                                label="Locked"
                                            />
                                            <Checkbox
                                                name="paid"
                                                label="Paid"
                                                readOnly={props.paid}
                                            />
                                            <Checkbox
                                                name="processed"
                                                label="Processed"
                                            />
                                        </div>
                                    </div>
                                </Card>

                                {props.payment?.stripe_status ===
                                    "succeeded" && (
                                    <Card
                                        title="Payment"
                                        footer={
                                            <Link
                                                className="text-sm"
                                                href={route(
                                                    "payments.payments.show",
                                                    [props.payment.id]
                                                )}
                                            >
                                                View payment or make refunds
                                            </Link>
                                        }
                                    >
                                        <div className="text-sm">
                                            <div className="text-gray-900 mb-4">
                                                Paid{" "}
                                                {props.payment.formatted_amount}
                                            </div>

                                            <div className="mb-4">
                                                {props.payment
                                                    .payment_method && (
                                                    <>
                                                        <div className="text-gray-900">
                                                            {
                                                                props.payment
                                                                    .payment_method
                                                                    .description
                                                            }
                                                        </div>
                                                        {props.payment
                                                            .payment_method
                                                            .information_line && (
                                                            <div className="text-gray-500">
                                                                {
                                                                    props
                                                                        .payment
                                                                        .payment_method
                                                                        .information_line
                                                                }
                                                            </div>
                                                        )}
                                                    </>
                                                )}
                                                {!props.payment
                                                    .payment_method && (
                                                    <div className="text-gray-900">
                                                        Payment method details
                                                        unavailable
                                                    </div>
                                                )}
                                            </div>

                                            <div className="text-gray-500">
                                                Total paid may include other
                                                competition entries or items.
                                            </div>
                                        </div>
                                    </Card>
                                )}

                                <Card title="Events">
                                    <BasicList
                                        items={props.entries.map((item) => {
                                            return {
                                                id: item.id,
                                                content: <>{item.event.name}</>,
                                            };
                                        })}
                                    />
                                </Card>
                            </div>
                        </div>

                        <div className="col-start-9 col-span-4">
                            <div className="grid gap-4">
                                <Card title="Entry information">
                                    <DefinitionList
                                        verticalPadding={2}
                                        items={[
                                            {
                                                key: "name",
                                                term: "Name",
                                                definition: `${props.entrant.first_name} ${props.entrant.last_name}`,
                                            },
                                            {
                                                key: "dob",
                                                term: "Date of birth",
                                                definition: formatDate(
                                                    props.entrant.date_of_birth
                                                ),
                                            },
                                            {
                                                key: "age",
                                                term: "Age",
                                                definition: props.entrant.age,
                                            },
                                            {
                                                key: "age_on_day",
                                                term: "Age on day",
                                                definition: `${
                                                    props.entrant.age_on_day
                                                } (${formatDate(
                                                    props.competition
                                                        .age_at_date
                                                )})`,
                                            },
                                            {
                                                key: "sex",
                                                term: "Competition category (sex)",
                                                definition: props.entrant.sex,
                                            },
                                        ]}
                                    />
                                </Card>

                                <Card title="Entry maker information">
                                    <DefinitionList
                                        verticalPadding={2}
                                        items={[
                                            {
                                                key: "header_name",
                                                term: "Entry maker name",
                                                definition: props.header.name,
                                            },
                                            {
                                                key: "header_email",
                                                term: "Entry maker email",
                                                definition: props.header.email,
                                                truncate: true,
                                            },
                                        ]}
                                    />
                                </Card>

                                <Card title="Entry metadata">
                                    <DefinitionList
                                        verticalPadding={2}
                                        items={[
                                            {
                                                key: "created_at",
                                                term: "Created at",
                                                definition: formatDateTime(
                                                    props.created_at
                                                ),
                                            },
                                            {
                                                key: "updated_at",
                                                term: "Updated at",
                                                definition: formatDateTime(
                                                    props.updated_at
                                                ),
                                            },
                                        ]}
                                    />
                                </Card>
                            </div>
                        </div>
                    </div>
                </Form>
            </Container>
        </>
    );
};

GuestEntryReadOnly.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default GuestEntryReadOnly;
