import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import Link from "@/Components/Link";
import { formatDate } from "@/Utils/date-utils";
import MainLayout from "@/Layouts/MainLayout";
import Badge from "@/Components/Badge";
import PlainCollection from "@/Components/PlainCollection";
import { usePage } from "@inertiajs/react";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import Checkbox from "@/Components/Form/Checkbox";
import FlashAlert from "@/Components/FlashAlert";
import useCustomFieldsList, {
    CustomFields,
} from "@/Components/Competitions/useCustomFieldsList";
import { DefinitionList } from "@/Components/DefinitionList";
import { LaravelPaginatorProps } from "@/Components/Collection.tsx";

interface EntryProps {
    id: string;
    entrant: {
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
        age: number;
        age_on_day: number;
        custom_fields: CustomFields;
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
        refunded: boolean;
        fully_refunded: boolean;
        event: {
            id: number;
            name: string;
        };
    }[];
    competition: {
        id: number;
        name: string;
    };
}

type GuestEntryListProps = {
    competition: {
        name: string;
        id: number;
    };
    entries: LaravelPaginatorProps<EntryProps>;
};

const EntryRenderer = (props: EntryProps): ReactNode => {
    const customFields = useCustomFieldsList(
        props.entrant.custom_fields,
        "show_in_preview",
    );

    // @ts-ignore Buried in props
    const competitionId = usePage().props.competition.id;

    return (
        <Form
            initialValues={{
                processed: props.processed,
                approved: props.processed,
                locked: props.locked,
                paid: props.paid,
                return_url: usePage().url,
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
            action={route("competitions.entries.show", [
                props.competition.id,
                props.id,
            ])}
            inertiaOptions={{
                preserveState: true,
                preserveScroll: true,
            }}
        >
            <div>
                <FlashAlert bag={props.id} className="mb-4" />
                <RenderServerErrors />
            </div>
            <div className="grid grid-cols-12 gap-4">
                <div className="col-span-full">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center min-w-0">
                            <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                                <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                                    {props.entrant.first_name}{" "}
                                    {props.entrant.last_name}
                                </div>
                                <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                                    {formatDate(props.entrant.date_of_birth)}{" "}
                                    (Age {props.entrant.age_on_day} on day)
                                </div>
                                <div>
                                    <DefinitionList
                                        verticalPadding={2}
                                        items={customFields}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-1">
                            {props.approved && (
                                <Badge colour="green">Approved</Badge>
                            )}
                            {props.paid && <Badge colour="yellow">Paid</Badge>}
                        </div>
                    </div>
                </div>
                <div className="col-start-1 col-span-6 text-sm text-gray-700">
                    <ul>
                        {props.entries.map((event_entry) => {
                            return (
                                <li key={event_entry.id}>
                                    {event_entry.event.name}{" "}
                                    {event_entry.fully_refunded && (
                                        <Badge colour="red">
                                            Fully refunded
                                        </Badge>
                                    )}
                                    {!event_entry.fully_refunded &&
                                        event_entry.refunded && (
                                            <Badge colour="yellow">
                                                Part refunded
                                            </Badge>
                                        )}
                                    <br />
                                    {event_entry.entry_time && (
                                        <>({event_entry.entry_time})</>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                </div>
                <div className="col-start-7 col-span-6 text-sm">
                    <div>Amount £{props.amount_string}</div>
                    <div>Amount refunded £{props.amount_refunded_string}</div>
                    <Checkbox name="approved" label="Approved" />
                    <Checkbox name="locked" label="Locked" />
                    <Checkbox
                        name="paid"
                        label="Paid"
                        readOnly={props.paid}
                        help={
                            props.paid
                                ? "Entries can not be marked as unpaid once marked as paid"
                                : null
                        }
                    />
                    <Checkbox name="processed" label="Processed" />
                </div>
                <div className="col-start-1 col-span-6 text-sm">
                    <Link
                        href={route("competitions.entries.show", {
                            competition: competitionId,
                            entry: props.id,
                        })}
                    >
                        View full entry details{" "}
                        <span aria-hidden="true"> &rarr;</span>
                    </Link>
                </div>
                <div className="col-start-7 col-span-6">
                    <SubmissionButtons />
                </div>
            </div>
        </Form>
    );
};

const MemberEntryList = (props: GuestEntryListProps) => {
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
                        name: "Entries",
                        route: "competitions.entries.index",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title="Entries"
                    subtitle={props.competition.name}
                ></MainHeader>

                <PlainCollection
                    {...props.entries}
                    itemRenderer={(item) => <EntryRenderer {...item} />}
                />
            </Container>
        </>
    );
};

MemberEntryList.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default MemberEntryList;
