import React from "react";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import BasicList from "@/Components/BasicList";
import Checkbox from "@/Components/Form/Checkbox";
import { EntryAdditionalDetails } from "@/Components/Competitions/EntryAdditionalDetails";

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

type EntryFormProps = {
    sessions: {
        id: number;
        name: string;
        venue: {
            id: number;
            name: string;
        };
        events: Event[];
    }[];
    action: string;
    readOnly: boolean;
};

export const EntryForm = ({ sessions, action, readOnly }: EntryFormProps) => {
    return (
        <>
            <Form
                initialValues={{}}
                validationSchema={yup.object().shape({
                    entries: yup.array().of(
                        yup.object().shape({
                            entering: yup.boolean(),
                            entry_time: yup
                                .string()
                                .nullable()
                                .matches(/^\d{0,2}:?\d{0,2}[.]\d{0,2}$/, {
                                    message:
                                        "Entry time must be of the format MM:SS.HH.",
                                    excludeEmptyString: true,
                                }),
                            amount: yup
                                .number()
                                .typeError("Amount must be a number.")
                                .min(0),
                        })
                    ),
                })}
                action={action}
                method="put"
                removeDefaultInputMargin
                submitTitle="Save"
                readOnly={readOnly}
                enableReinitialize={false}
            >
                <div className="grid gap-4">
                    {sessions.map((session) => (
                        <Card key={session.id} title={session.name}>
                            <BasicList
                                items={session.events.map((event) => {
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
                                                            name={`entries.${
                                                                event.sequence -
                                                                1
                                                            }.entering`}
                                                            label={event.name}
                                                            help={`£${event.entry_fee_string} entry fee, £${event.processing_fee_string} processing fee`}
                                                            mb="mb-0"
                                                        />
                                                    </div>

                                                    <div className="col-span-full @lg:col-span-7">
                                                        <EntryAdditionalDetails
                                                            event={event}
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
                </div>
            </Form>
        </>
    );
};
