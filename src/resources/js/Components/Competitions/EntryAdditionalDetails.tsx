import React from "react";
import { Event } from "@/Components/Competitions/EntryForm";
import { useFormikContext } from "formik";
import TextInput from "@/Components/Form/TextInput";
import DecimalInput from "@/Components/Form/DecimalInput";

type EntryAdditionalDetailsProps = {
    /** The event object from the entry form */
    event: Event;
    requireTimes: boolean;
};

export const EntryAdditionalDetails = ({
    event,
    requireTimes,
}: EntryAdditionalDetailsProps) => {
    const { values }: { values: { entries: { entering: boolean }[] } } =
        useFormikContext();
    const selected: boolean = values.entries?.[event.sequence - 1]?.entering;

    if (selected) {
        return (
            <div className="grid gap-4">
                {requireTimes && (
                    <div>
                        <TextInput
                            name={`entries.${event.sequence - 1}.entry_time`}
                            label="Entry time"
                            help={`An entry time is required for this event. Please enter your personal best time, if you have one.`}
                        />
                    </div>
                )}

                {false && (
                    <div>
                        <DecimalInput
                            name={`entries.${event.sequence - 1}.amount`}
                            label="Amount (£)"
                            help={`Edit the combined event and processing fees.`}
                        />
                    </div>
                )}
            </div>
        );
    }

    return null;
};
