import React from "react";
import Form, { SubmissionButtons } from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import BasicList from "@/Components/BasicList";
import Checkbox from "@/Components/Form/Checkbox";
import { EntryAdditionalDetails } from "@/Components/Competitions/EntryAdditionalDetails";
import Container from "@/Components/Container";
import { usePage } from "@inertiajs/react";
import Alert from "@/Components/Alert";
import Button from "@/Components/Button";
import { router } from "@inertiajs/react";

export type Event = {
    id: number;
    name: string;
    stroke: string;
    units: string;
    distance: number;
    event_code: number;
    sequence: number;
    ages: string[];
    entry_fee: number;
    processing_fee: number;
    entry_fee_string: string;
    processing_fee_string: string;
    category: string;
};

export type Session = {
    id: number;
    name: string;
    venue: {
        id: number;
        name: string;
    };
    events: Event[];
};

type EntryFormProps = {
    sessions: Session[];
    action: string;
    readOnly: boolean;
    requireTimes: boolean;
    /** Whether coaches select entries for this competition */
    coachEnters?: boolean;
    /** Whether the current user has coach permissions */
    isCoach?: boolean;
    vetoable?: boolean;
    locked?: boolean;
    vetoRoute?: string;
};

const findInitialValuesArrayId = (
    event_id: number,
    initialEventValues: { event_id: number }[],
): number => {
    return initialEventValues.findIndex((event) => {
        return event.event_id === event_id;
    });
};

export const EntryForm = ({
    sessions,
    action,
    readOnly,
    requireTimes,
    coachEnters,
    isCoach,
    locked,
    vetoable,
    vetoRoute,
}: EntryFormProps) => {
    // @ts-ignore
    const initialEventValues = usePage().props.form_initial_values?.entries;

    const validationSchema: { [key: string]: yup.Schema<any> } = {
        entries: yup.array().of(
            yup.object().shape({
                entering: yup.boolean(),
                entry_time: yup
                    .string()
                    .nullable()
                    .matches(/^\d{0,2}:?\d{0,2}[.]\d{0,2}$/, {
                        message: "Entry time must be of the format MM:SS.HH.",
                        excludeEmptyString: true,
                    }),
                amount: yup
                    .number()
                    .typeError("Amount must be a number.")
                    .min(0),
            }),
        ),
    };

    if (isCoach && coachEnters) {
        validationSchema.vetoable = yup.boolean();
        validationSchema.locked = yup.boolean();
    }

    return (
        <>
            <Form
                initialValues={{}}
                validationSchema={yup.object().shape(validationSchema)}
                action={action}
                method="put"
                removeDefaultInputMargin
                submitTitle="Save"
                readOnly={readOnly}
                enableReinitialize={false}
                hideDefaultButtons
            >
                <Container noMargin>
                    <div className="grid gap-4">
                        {sessions.map((session) => (
                            <Card key={session.id} title={session.name}>
                                <BasicList
                                    items={session.events.map((event) => {
                                        const vid = findInitialValuesArrayId(
                                            event.id,
                                            initialEventValues,
                                        );

                                        return {
                                            id: event.id,
                                            content: (
                                                <div
                                                    key={event.id}
                                                    className="@container"
                                                >
                                                    <div className="grid gap-4 grid-cols-12">
                                                        <div className="col-span-full @lg:col-span-5">
                                                            <Checkbox
                                                                name={`entries.${vid}.entering`}
                                                                label={
                                                                    event.name
                                                                }
                                                                help={`£${event.entry_fee_string} entry fee, £${event.processing_fee_string} processing fee`}
                                                                mb="mb-0"
                                                            />
                                                        </div>

                                                        <div className="col-span-full @lg:col-span-7">
                                                            <EntryAdditionalDetails
                                                                event={event}
                                                                requireTimes={
                                                                    requireTimes
                                                                }
                                                                vid={vid}
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            ),
                                        };
                                    })}
                                ></BasicList>
                            </Card>
                        ))}

                        {isCoach && coachEnters && (
                            <Card title="Entry options">
                                <Checkbox
                                    label="Vetoable"
                                    name="vetoable"
                                    help="Allow parents to reject your entries and not enter the gala"
                                />

                                <Checkbox
                                    label="Lock entry"
                                    name="locked"
                                    help="Prevent parents from editing your entries"
                                />
                            </Card>
                        )}

                        {coachEnters && isCoach && locked && (
                            <Alert
                                variant="warning"
                                title="This entry has been locked by your coach"
                            >
                                <p>
                                    You can't make any changes to the selected
                                    events. Please speak to your coach if you
                                    want to make alterations.
                                </p>
                            </Alert>
                        )}

                        {coachEnters && isCoach && vetoable && (
                            <Alert
                                variant="warning"
                                title="This entry can be vetoed"
                            >
                                <p className="mb-3">
                                    If you aren't happy with the selected
                                    events, you can reject this entry entirely.
                                </p>

                                <p>
                                    <Button
                                        variant="warning"
                                        onClick={() => {
                                            router.delete(vetoRoute);
                                        }}
                                    >
                                        Veto entry
                                    </Button>
                                </p>
                            </Alert>
                        )}
                    </div>
                </Container>

                <Container>
                    <div className="mt-4">
                        <SubmissionButtons />
                    </div>
                </Container>
            </Form>
        </>
    );
};
