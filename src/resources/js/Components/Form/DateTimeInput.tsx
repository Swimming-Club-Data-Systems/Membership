import React, { ReactNode, useContext, useMemo } from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";
import { registerLocale } from "react-datepicker";
import { enGB } from "date-fns/locale";
import { parseISO, parse } from "date-fns";
import { format, zonedTimeToUtc, utcToZonedTime } from "date-fns-tz";
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
    timezones?: DateTimeInputTimezones;
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
    timezones = false,
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

    const calculatedValue = parseISO(field.value);
    // if (props.timeZone) {
    //     calculatedValue = utcToZonedTime(field.value, props.timeZone);
    // }

    const dateValue = useMemo(() => {
        try {
            return {
                startDate: format(calculatedValue, "yyyy-MM-dd"),
                endDate: format(calculatedValue, "yyyy-MM-dd"),
            };
        } catch {
            return {
                startDate: null,
                endDate: null,
            };
        }
    }, [calculatedValue]);

    const timeValue = useMemo(() => {
        try {
            return format(calculatedValue, "HH:mm");
        } catch {
            return null;
        }
    }, [calculatedValue]);

    const handleValueChange = (
        newValue:
            | { startDate: string; endDate: string }
            | { target: { value: string } },
        type: "date" | "time"
    ) => {
        // Calculate the new datetime
        const formatTime = (date) => {
            // Force to 00:00:00 if this is only a date input
            if (!showTimeInput) {
                return "00:00:00";
            }

            try {
                return format(date, "HH:mm:ss");
            } catch {
                return "00:00:00";
            }
        };

        const formatDate = (date) => {
            try {
                return format(date, "yyyy-MM-dd");
            } catch {
                return format(new Date(), "yyyy-MM-dd");
            }
        };

        let newDate: string;
        if (type === "date") {
            newDate = newValue?.startDate + " " + formatTime(calculatedValue);
        } else if (type === "time") {
            newDate =
                formatDate(calculatedValue) +
                " " +
                format(
                    parse(newValue.target?.value, "HH:mm", new Date()),
                    "HH:mm:ss"
                );
        }

        field.onChange({
            target: {
                name: props.name,
                value: newDate,
            },
        });
    };

    const handleDateValueChange = (newValue) => {
        handleValueChange(newValue, "date");
    };

    const handleTimeValueChange = (newValue) => {
        handleValueChange(newValue, "time");
    };

    return (
        <>
            <div className="@container flex gap-4">
                <div>
                    <BaseInput
                        label={label}
                        type={type}
                        // inputClassName="w-44"
                        shadow={false}
                        input={
                            <div className="flex flex-col @[17rem]:flex-row gap-4">
                                <div className="w-full @[17rem]:max-w-44">
                                    <TailwindDatepicker
                                        inputId={controlId}
                                        inputClassName={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
                                        primaryColor="indigo"
                                        asSingle={true}
                                        useRange={false}
                                        value={dateValue}
                                        onChange={handleDateValueChange}
                                        minDate={minDate}
                                        maxDate={maxDate}
                                        readOnly={readOnly}
                                        disabled={
                                            isSubmitting ||
                                            disabled ||
                                            context.disabled
                                        }
                                    />
                                </div>
                                {showTimeInput && (
                                    <div className="">
                                        <div className="max-w-20">
                                            <Input
                                                id={`${controlId}_time`}
                                                type="time"
                                                className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
                                                value={timeValue}
                                                onBlur={() =>
                                                    field.onBlur({
                                                        target: {
                                                            name: props.name,
                                                        },
                                                    })
                                                }
                                                onChange={handleTimeValueChange}
                                                readOnly={readOnly}
                                                disabled={
                                                    isSubmitting ||
                                                    disabled ||
                                                    context.disabled
                                                }
                                            />
                                        </div>
                                    </div>
                                )}
                                {/*<DatePicker*/}
                                {/*    id={controlId}*/}
                                {/*    name={props.name}*/}
                                {/*    customInput={*/}
                                {/*        <Input*/}
                                {/*            type={type}*/}
                                {/*            readOnly={readOnly}*/}
                                {/*            disabled={*/}
                                {/*                isSubmitting ||*/}
                                {/*                disabled ||*/}
                                {/*                context.disabled*/}
                                {/*            }*/}
                                {/*            className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}*/}
                                {/*            id={controlId}*/}
                                {/*            {...props}*/}
                                {/*        />*/}
                                {/*    }*/}
                                {/*    selected={calculatedValue}*/}
                                {/*    onChange={(value) => {*/}
                                {/*        console.log(*/}
                                {/*            Intl.DateTimeFormat().resolvedOptions()*/}
                                {/*        );*/}
                                {/*        console.log(*/}
                                {/*            value,*/}
                                {/*            zonedTimeToUtc(*/}
                                {/*                value,*/}
                                {/*                Intl.DateTimeFormat().resolvedOptions()*/}
                                {/*                    .timeZone*/}
                                {/*            )*/}
                                {/*        );*/}
                                {/*        field.onChange({*/}
                                {/*            target: {*/}
                                {/*                name: props.name,*/}
                                {/*                value: format(*/}
                                {/*                    value,*/}
                                {/*                    "yyyy-MM-dd HH:mm:ss",*/}
                                {/*                    {*/}
                                {/*                        timeZone:*/}
                                {/*                            props.timeZone || "UTC",*/}
                                {/*                    }*/}
                                {/*                ),*/}
                                {/*            },*/}
                                {/*        });*/}
                                {/*    }}*/}
                                {/*    onBlur={(event) => {*/}
                                {/*        field.onBlur({*/}
                                {/*            target: {*/}
                                {/*                name: props.name,*/}
                                {/*            },*/}
                                {/*        });*/}

                                {/*        /**/}
                                {/*    if (event.target.value) {*/}
                                {/*        try {*/}
                                {/*            const value = formatISO(*/}
                                {/*                new Date(event.target.value)*/}
                                {/*            );*/}

                                {/*            field.onBlur({*/}
                                {/*                target: {*/}
                                {/*                    name: props.name,*/}
                                {/*                    value: value,*/}
                                {/*                },*/}
                                {/*            });*/}
                                {/*        } catch (error) {*/}
                                {/*            // Ignore*/}
                                {/*            console.log(error);*/}
                                {/*        }*/}
                                {/*    } else {*/}
                                {/*        field.onBlur({*/}
                                {/*            target: {*/}
                                {/*                name: props.name,*/}
                                {/*            },*/}
                                {/*        });*/}
                                {/*    }*/}
                                {/*    */}
                                {/*    }}*/}
                                {/*    showTimeInput={showTimeInput}*/}
                                {/*    locale="en-GB"*/}
                                {/*    dateFormat={dateFormat}*/}
                                {/*    minDate={minDate}*/}
                                {/*    maxDate={maxDate}*/}
                                {/*    showMonthDropdown*/}
                                {/*    showYearDropdown*/}
                                {/*/>*/}
                            </div>
                        }
                        rightButton={rightButton}
                        {...props}
                    />
                </div>

                {timezones && (
                    <div className="w-48">
                        <Select
                            name={`${props.name}_timezone`}
                            label={`${label} timezone`}
                            items={timezones}
                        />
                    </div>
                )}
            </div>
        </>
    );
};

export default DateTimeInput;
