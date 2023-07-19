import React from "react";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import Card from "@/Components/Card";
import BasicList from "@/Components/BasicList";
import Checkbox from "@/Components/Form/Checkbox";

type Props = {
    sessions: {
        id: number;
        name: string;
        venue: {
            id: number;
            name: string;
        };
        events: {
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
        }[];
    }[];
};

export const EntryForm = ({ sessions }: Props) => {
    return (
        <>
            <Form
                initialValues={{}}
                validationSchema={yup.object().shape({
                    entries: yup.array().of(
                        yup.object().shape({
                            entered: yup.boolean(),
                        })
                    ),
                })}
            >
                {/*  Loop over each session rendering checkboxes  */}

                <div className="grid gap-4">
                    {sessions.map((session) => (
                        <Card key={session.id} title={session.name}>
                            <BasicList
                                items={session.events.map((event) => {
                                    return {
                                        id: event.id,
                                        content: (
                                            <div key={event.id}>
                                                <Checkbox
                                                    name={`event${event.id}.enter`}
                                                    label={event.name}
                                                    help={`£${event.entry_fee_string} entry fee, £${event.processing_fee_string} processing fee`}
                                                />
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
