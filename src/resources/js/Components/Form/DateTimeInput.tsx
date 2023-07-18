import React, { ReactNode, useContext } from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";
import formatISO from "date-fns/formatISO";
import DatePicker from "react-datepicker";
import { registerLocale } from "react-datepicker";
// import "react-datepicker/dist/react-datepicker.css";
import "./DateTimeInput.css";
import { enGB } from "date-fns/locale";
registerLocale("en-GB", enGB);

interface Props extends InputProps {
    disabled?: boolean;
    className?: string;
    id?: string;
    name: string;
    label: string;
    autoComplete?: string;
    leftText?: ReactNode;
    rightButton?: ReactNode;
    showTimeInput?: boolean;
    min: string;
    max: string;
}

const DateTimeInput: React.FC<Props> = ({
    label,
    disabled,
    type,
    leftText,
    rightButton,
    className = "",
    showTimeInput = false,
    min,
    max,
    ...props
}) => {
    const [{ ...field }, meta, helpers] = useField(props);
    const { isSubmitting } = useFormikContext();
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);
    const minDate = typeof min === "string" ? new Date(min) : min;
    const maxDate = typeof max === "string" ? new Date(max) : max;

    if (!type) {
        type = "text";
    }

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
    }

    if (!leftText) {
        className += " rounded-l-md ";
    }

    if (!rightButton) {
        className += " rounded-r-md ";
    }

    const dateFormat = showTimeInput ? "dd/MM/yyyy HH:mm" : "dd/MM/yyyy";

    return (
        <>
            <BaseInput
                label={label}
                type={type}
                inputClassName="w-44"
                input={
                    <DatePicker
                        id={controlId}
                        name={props.name}
                        customInput={
                            <Input
                                type={type}
                                readOnly={readOnly}
                                disabled={
                                    isSubmitting || disabled || context.disabled
                                }
                                className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                                id={controlId}
                                {...props}
                            />
                        }
                        selected={new Date(field.value)}
                        onChange={(value) => {
                            field.onChange({
                                target: {
                                    name: props.name,
                                    value: formatISO(value),
                                },
                            });
                        }}
                        onBlur={(event) => {
                            field.onBlur({
                                target: {
                                    name: props.name,
                                },
                            });

                            /*
                            if (event.target.value) {
                                try {
                                    const value = formatISO(
                                        new Date(event.target.value)
                                    );

                                    field.onBlur({
                                        target: {
                                            name: props.name,
                                            value: value,
                                        },
                                    });
                                } catch (error) {
                                    // Ignore
                                    console.log(error);
                                }
                            } else {
                                field.onBlur({
                                    target: {
                                        name: props.name,
                                    },
                                });
                            }
                            */
                        }}
                        showTimeInput={showTimeInput}
                        locale="en-GB"
                        dateFormat={dateFormat}
                        minDate={minDate}
                        maxDate={maxDate}
                        showMonthDropdown
                        showYearDropdown
                    />
                }
                rightButton={rightButton}
                {...props}
            />
        </>
    );
};

export default DateTimeInput;
