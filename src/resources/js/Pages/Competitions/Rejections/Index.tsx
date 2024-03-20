import React, { ReactNode, useRef, useState } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import Link from "@/Components/Link";
import { formatDate } from "@/Utils/date-utils";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import Form, { RenderServerErrors } from "@/Components/Form/Form";
import * as yup from "yup";
import Checkbox from "@/Components/Form/Checkbox";
import FlashAlert from "@/Components/FlashAlert";
import { BasicListTwo, BasicListTwoItem } from "@/Components/BasicListTwo";
import { DefinitionList } from "@/Components/DefinitionList";
import Modal from "@/Components/Modal";
import Select from "@/Components/Form/Select";
import { CancellationReasonsSelectItems } from "@/Utils/Competitions/CancellationReasons";
import Button from "@/Components/Button";
import { ReceiptRefundIcon } from "@heroicons/react/24/outline";
import Alert from "@/Components/Alert";
import { LaravelPaginatorProps } from "@/Components/Collection.tsx";

interface EntrantProps {
    id: string;
    first_name: string;
    last_name: string;
    date_of_birth: string;
    sex: string;
    age: number;
    age_on_day: number;
    competition: {
        id: number;
        name: string;
    };
    entries: {
        id: string;
        member_MemberID: number;
        competition_guest_entrant_id: string;
        paid: boolean;
        processed: boolean;
        amount: number;
        amount_refunded: number;
        amount_formatted: number;
        amount_refunded_formatted: number;
        vetoable: boolean;
        approved: boolean;
        locked: boolean;
        created_at: string;
        updated_at: string;
        competition_id: number;
        processing_fee_paid: boolean;
        editable: boolean;
        competition_event_entries: {
            id: string;
            competition_entry_id: string;
            competition_event_id: number;
            entry_time: number;
            amount: number;
            amount_refunded: number;
            amount_formatted: number;
            amount_refunded_formatted: number;
            cancellation_reason: string;
            notes: string;
            created_at: string;
            updated_at: string;
            paid: boolean;
            competition_event: {
                ages: string[];
                category: string;
                distance: number;
                entry_fee: number;
                event_code: string;
                id: number;
                name: string;
                processing_fee: number;
                stroke: string;
                units: string;
            };
        }[];
    }[];
}

type GuestEntryListProps = {
    competition: {
        name: string;
        id: number;
    };
    entrants: LaravelPaginatorProps<EntrantProps>;
};

const EntrantRenderer = (props: EntrantProps): ReactNode => {
    const [showDialog, setShowDialog] = useState<boolean>(false);

    return (
        <>
            <div>
                <FlashAlert bag={props.id} className="mb-4" />
                <RenderServerErrors />
            </div>
            <div className="grid grid-cols-12 gap-4">
                <div className="col-span-full">
                    <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                        {props.first_name} {props.last_name}
                    </div>
                    <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                        {formatDate(props.date_of_birth)} (Age{" "}
                        {props.age_on_day} on day)
                    </div>
                    <BasicListTwo>
                        {props.entries.map((entry) => {
                            const initialValues = {
                                events: entry.competition_event_entries
                                    .filter(
                                        (eventEntry) =>
                                            !eventEntry.cancellation_reason,
                                    )
                                    .map((ev) => {
                                        return {
                                            is_to_refund: false,
                                            refund_reason: "",
                                            event_entry_id: ev.id,
                                        };
                                    }),
                            };

                            return (
                                <BasicListTwoItem key={entry.id}>
                                    <div className="text-sm text-gray-700">
                                        {!entry.paid && (
                                            <Alert
                                                variant="warning"
                                                title="Entry can not be refunded"
                                                className="mb-4"
                                            >
                                                This entry can not yet be fully
                                                or partially refunded as it has
                                                not been paid for.
                                            </Alert>
                                        )}

                                        <DefinitionList
                                            items={[
                                                {
                                                    key: "entry_id",
                                                    term: "Entry ID",
                                                    definition: entry.id,
                                                },
                                                {
                                                    key: "paid",
                                                    term: "Paid",
                                                    definition: entry.paid
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "processed",
                                                    term: "Processed",
                                                    definition: entry.processed
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "amount",
                                                    term: "Amount",
                                                    definition:
                                                        entry.amount_formatted,
                                                },
                                                {
                                                    key: "amount_refunded",
                                                    term: "Amount refunded",
                                                    definition:
                                                        entry.amount_refunded_formatted,
                                                },
                                            ]}
                                        />

                                        <div className="flex items-center gap-3 mt-4">
                                            {entry.paid && (
                                                <Button
                                                    variant="secondary"
                                                    onClick={() =>
                                                        setShowDialog(true)
                                                    }
                                                >
                                                    Manage refunds
                                                </Button>
                                            )}

                                            <Link
                                                href={route(
                                                    "competitions.entries.show",
                                                    {
                                                        competition:
                                                            entry.competition_id,
                                                        entry: entry.id,
                                                    },
                                                )}
                                                target="_blank"
                                            >
                                                View full entry details{" "}
                                                <span aria-hidden="true">
                                                    {" "}
                                                    &rarr;
                                                </span>
                                            </Link>
                                        </div>
                                    </div>

                                    <Modal
                                        onClose={() => setShowDialog(false)}
                                        title="Select events to refund"
                                        show={showDialog}
                                        Icon={ReceiptRefundIcon}
                                    >
                                        <Form
                                            initialValues={initialValues}
                                            validationSchema={yup
                                                .object()
                                                .shape({
                                                    events: yup.array().of(
                                                        yup.object().shape({
                                                            is_to_refund:
                                                                yup.boolean(),
                                                            refund_reason: yup
                                                                .string()
                                                                .when(
                                                                    "is_to_refund",
                                                                    {
                                                                        is: true,
                                                                        then: (
                                                                            schema,
                                                                        ) =>
                                                                            schema.required(
                                                                                "A refund reason is required.",
                                                                            ),
                                                                    },
                                                                ),
                                                        }),
                                                    ),
                                                })}
                                            method="post"
                                            action={route(
                                                "competitions.rejections.refund",
                                                {
                                                    competition:
                                                        props.competition.id,
                                                    entry: entry.id,
                                                },
                                            )}
                                        >
                                            <RenderServerErrors />
                                            <FlashAlert />

                                            <BasicListTwo>
                                                {entry.competition_event_entries
                                                    .filter(
                                                        (eventEntry) =>
                                                            !eventEntry.cancellation_reason,
                                                    )
                                                    .map((eventEntry, idx) => {
                                                        return (
                                                            <BasicListTwoItem
                                                                key={
                                                                    eventEntry.id
                                                                }
                                                            >
                                                                <Checkbox
                                                                    name={`events.${idx}.is_to_refund`}
                                                                    label={
                                                                        eventEntry
                                                                            .competition_event
                                                                            .name
                                                                    }
                                                                    help={`${eventEntry.amount_formatted} to be refunded`}
                                                                />

                                                                <Select
                                                                    name={`events.${idx}.refund_reason`}
                                                                    label="Refund reason"
                                                                    items={
                                                                        CancellationReasonsSelectItems
                                                                    }
                                                                />
                                                            </BasicListTwoItem>
                                                        );
                                                    })}
                                            </BasicListTwo>
                                        </Form>
                                    </Modal>
                                </BasicListTwoItem>
                            );
                        })}
                    </BasicListTwo>
                </div>
            </div>
        </>
    );
};

const Index = (props: GuestEntryListProps) => {
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
                        name: "Manage Rejections",
                        route: "competitions.rejections.index",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Manage Rejections"
                    subtitle={props.competition.name}
                ></MainHeader>

                <PlainCollection
                    {...props.entrants}
                    itemRenderer={EntrantRenderer}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
