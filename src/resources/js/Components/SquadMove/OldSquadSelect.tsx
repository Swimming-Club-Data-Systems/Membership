import React, { useEffect, useState } from "react";
import { useField } from "formik";
import axios from "@/Utils/axios";
import Select from "@/Components/Form/Select";

type OldSquadSelectProps = {
    memberId?: number;
};

export const OldSquadSelect = (props: OldSquadSelectProps) => {
    const [values, setValues] = useState([]);
    const [valuesLoaded, setValuesLoaded] = useState(false);
    const [{ value: fieldMemberId }] = useField("member");
    const [{ value }, , { setValue }] = useField("old_squad");

    const memberId = props.memberId || fieldMemberId;

    useEffect(() => {
        // Get the member's squads
        if (memberId) {
            axios.get(route("members.squads", memberId)).then((result) => {
                setValues(result?.data ?? []);
                setValuesLoaded(true);
            });
        }
    }, [memberId]);

    useEffect(() => {
        if (value && valuesLoaded) {
            // If the squad currently selected is not in the array, set the field value to null
            if (!values.find((element) => element.value === value)) {
                setValue(null);
            }
        }
    }, [setValue, value, values, valuesLoaded]);

    return (
        <Select name="old_squad" items={values} label="Old squad" nullable />
    );
};
