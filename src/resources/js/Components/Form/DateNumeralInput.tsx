import React, { ReactNode, useContext, useRef } from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";
import { registerLocale } from "react-datepicker";
import { enGB } from "date-fns/locale";
import { format, parseISO, parse } from "date-fns";
import TailwindDatepicker from "react-tailwindcss-datepicker";
import Select from "@/Components/Form/Select";
registerLocale("en-GB", enGB);

export type DateTimeInputTimezone = {
    key: string;
    name: string;
    value: string;
    disabled?: boolean;
};

export type DateTimeInputTimezones = DateTimeInputTimezone[];

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
    min?: string;
    max?: string;
}

const DateNumeralInput: React.FC<Props> = ({
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

    const yearRef = useRef<HTMLInputElement | null>();
    const monthRef = useRef<HTMLInputElement | null>();
    const dayRef = useRef<HTMLInputElement | null>();

    if (!type) {
        type = "text";
    }

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
    }

    if (!leftText) {
        className += " rounded-l-md ";
    }

    if (!rightButton) {
        className += " rounded-r-md ";
    }

    const dateValues = field.value.split("T");
    const values = dateValues[0].split("-");

    const year = values[0];
    const month = values[1];
    const day = values[2];

    const handleValueChange = (
        newValue: { target: { value: string } },
        type: "year" | "month" | "day"
    ) => {
        // Calculate date

        let newYear = year;
        let newMonth = month;
        let newDay = day;

        if (type === "year") {
            newYear = newValue.target.value; //.padStart(4, "0");
        } else if (type === "month") {
            newMonth = newValue.target.value; //.padStart(2, "0");
        } else if (type === "day") {
            newDay = newValue.target.value; //.padStart(2, "0");
        }

        field.onChange({
            target: {
                name: props.name,
                value: newYear + "-" + newMonth + "-" + newDay,
            },
        });
    };

    const handleYearChange = (v) => handleValueChange(v, "year");
    const handleMonthChange = (v) => handleValueChange(v, "month");
    const handleDayChange = (v) => handleValueChange(v, "day");

    const handleBlur = () => {
        setTimeout(() => {
            const hasBlurred = !(
                yearRef.current === document.activeElement ||
                monthRef.current === document.activeElement ||
                dayRef.current === document.activeElement
            );

            if (hasBlurred) {
                field.onBlur({
                    target: {
                        name: props.name,
                    },
                });
            }
        }, 5);
    };

    return (
        <>
            {/*@container class removed due to z-index issues*/}
            <div className="flex gap-4">
                <div>
                    <BaseInput
                        label={label}
                        type={type}
                        // inputClassName="w-44"
                        shadow={false}
                        showErrorIconOnLabel={true}
                        input={
                            <div className="flex flex-row gap-2">
                                <div className="w-20">
                                    <label
                                        htmlFor={`${controlId}_year`}
                                        className="block text-sm mb-1 font-medium text-gray-500"
                                    >
                                        Year
                                    </label>
                                    <Input
                                        id={`${controlId}_year`}
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
                                        value={year}
                                        onBlur={handleBlur}
                                        onChange={handleYearChange}
                                        readOnly={readOnly}
                                        disabled={
                                            isSubmitting ||
                                            disabled ||
                                            context.disabled
                                        }
                                        pattern="[0-9]{4}"
                                        ref={yearRef}
                                    />
                                </div>
                                <div className="w-14">
                                    <label
                                        htmlFor={`${controlId}_month`}
                                        className="block text-sm mb-1 font-medium text-gray-500"
                                    >
                                        Month
                                    </label>
                                    <Input
                                        id={`${controlId}_month`}
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
                                        value={month}
                                        onBlur={handleBlur}
                                        onChange={handleMonthChange}
                                        readOnly={readOnly}
                                        disabled={
                                            isSubmitting ||
                                            disabled ||
                                            context.disabled
                                        }
                                        pattern="[0-9]{2}"
                                        ref={monthRef}
                                    />
                                </div>
                                <div className="w-14">
                                    <label
                                        htmlFor={`${controlId}_day`}
                                        className="block text-sm mb-1 font-medium text-gray-500"
                                    >
                                        Day
                                    </label>
                                    <Input
                                        id={`${controlId}_day`}
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
                                        value={day}
                                        onBlur={handleBlur}
                                        onChange={handleDayChange}
                                        readOnly={readOnly}
                                        disabled={
                                            isSubmitting ||
                                            disabled ||
                                            context.disabled
                                        }
                                        pattern="[0-9]{2}"
                                        ref={dayRef}
                                    />
                                </div>
                            </div>
                        }
                        rightButton={rightButton}
                        {...props}
                    />
                </div>
            </div>
        </>
    );
};

export default DateNumeralInput;
