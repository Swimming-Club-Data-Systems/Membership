import React, { ReactNode, useContext, useEffect, useState } from "react";
import { useField, useFormik, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";
import NativeDateInput from "@/Components/Form/NativeDateInput";
import TextInput from "@/Components/Form/TextInput";
import formatISO from "date-fns/formatISO";
import { format, parse, set } from "date-fns";

interface Props extends InputProps {
    disabled?: boolean;
    className?: string;
    id?: string;
    name: string;
    label: string;
    autoComplete?: string;
}

const DateTimeInput: React.FC<Props> = ({ label, disabled, ...props }) => {
    const [{ ...field }, meta] = useField(props);
    const { setFieldValue, setFieldTouched } = useFormikContext();
    const { formName } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 rounded-l-md rounded-r-md";
    }

    const [localDate, setLocalDate] = useState(null);

    useEffect(() => {
        try {
            const date = new Date(field.value);
            setFieldValue(
                `x:${controlId}-internal-field-date`,
                formatISO(date, { representation: "date" })
            );
            setFieldValue(
                `x:${controlId}-internal-field-time`,
                format(date, "kk:mm:ss")
            );
            setLocalDate(date);
        } catch (err) {
            const date = new Date();
            setFieldValue(
                `x:${controlId}-internal-field-date`,
                formatISO(date, { representation: "date" })
            );
            setFieldValue(
                `x:${controlId}-internal-field-time`,
                format(date, "kk:mm:ss")
            );
            setLocalDate(date);
        }
    }, [field.value]);

    return (
        <div className="flex gap-6">
            <NativeDateInput
                name={`x:${controlId}-internal-field-date`}
                label="Date"
                onBlur={(ev) => {
                    const date = new Date(ev.target.value);
                    const newDate = set(localDate, {
                        year: date.getFullYear(),
                        month: date.getMonth(),
                        date: date.getDate(),
                    });
                    setFieldValue(props.name, newDate.toISOString());
                    setLocalDate(newDate);
                    setFieldTouched(props.name);
                }}
                disabled={disabled}
                mb="mb-0"
            />
            <TextInput
                name={`x:${controlId}-internal-field-time`}
                label="Time"
                type="time"
                onBlur={(ev) => {
                    const date = parse(ev.target.value, "kk:mm:ss", new Date());
                    const newDate = set(localDate, {
                        hours: date.getHours(),
                        minutes: date.getMinutes(),
                        seconds: date.getSeconds(),
                    });
                    console.log(date, newDate);
                    setFieldValue(props.name, newDate.toISOString());
                    setLocalDate(newDate);
                    setFieldTouched(props.name);
                }}
                disabled={disabled}
                mb="mb-0"
            />
        </div>
    );
};

export default DateTimeInput;
