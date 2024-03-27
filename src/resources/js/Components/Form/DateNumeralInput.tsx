import React, {
    ReactNode,
    useContext,
    useEffect,
    useRef,
    useState,
} from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";
import { registerLocale } from "react-datepicker";
import { enGB } from "date-fns/locale";
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
    const [{ ...field }, meta] = useField<string>(props);
    const { isSubmitting } = useFormikContext();
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);

    const yearRef = useRef<HTMLInputElement | null>();
    const monthRef = useRef<HTMLInputElement | null>();
    const dayRef = useRef<HTMLInputElement | null>();

    const [year, setYear] = useState<string>("");
    const [month, setMonth] = useState<string>("");
    const [day, setDay] = useState<string>("");

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

    // let year = "";
    // let month = "";
    // let day = "";
    // try {
    //     if (field.value) {
    //         const date = new Date(field.value);
    //         year = date.getFullYear().toString();
    //         month = (date.getMonth() + 1).toString();
    //         day = date.getDate().toString();
    //     }
    // } catch (e) {
    //     console.error(e);
    // }

    useEffect(() => {
        if (field.value) {
            try {
                const date = new Date(field.value);
                if (Number.isNaN(date.getFullYear())) {
                    throw new Error("Invalid date");
                }
                setYear(date.getFullYear().toString().padStart(4, "0"));
                setMonth((date.getMonth() + 1).toString().padStart(2, "0"));
                setDay(date.getDate().toString().padStart(2, "0"));
            } catch (e) {
                setYear("");
                setMonth("");
                setDay("");
            }
        } else {
            setYear("");
            setMonth("");
            setDay("");
        }
    }, [field.value]);

    const handleValueChange = (
        newValue: { target: { value: string } },
        type: "year" | "month" | "day",
    ) => {
        // Calculate date

        let newYear = year;
        let newMonth = month;
        let newDay = day;

        if (type === "year") {
            newYear = newValue.target.value; //.padStart(4, "0");
            setYear(newYear);
        } else if (type === "month") {
            newMonth = newValue.target.value; //.padStart(2, "0");
            setMonth(newMonth);
        } else if (type === "day") {
            newDay = newValue.target.value; //.padStart(2, "0");
            setDay(newDay);
        }
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
                // Create date
                try {
                    const date = new Date(
                        parseInt(year),
                        parseInt(month) - 1,
                        parseInt(day),
                    );

                    field.onChange({
                        target: {
                            name: props.name,
                            value:
                                date.getFullYear() +
                                "-" +
                                (date.getMonth() + 1)
                                    .toString()
                                    .padStart(2, "0") +
                                "-" +
                                date.getDate().toString().padStart(2, "0"),
                        },
                    });
                } catch (e) {
                    // console.error(e);
                }

                setTimeout(() => {
                    field.onBlur({
                        target: {
                            name: props.name,
                        },
                    });
                }, 5);
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
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
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
                                        inputMode="numeric"
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
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
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
                                        inputMode="numeric"
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
                                        className={`flex-1 min-w-0 w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 disabled:bg-gray-100 shadow-sm ${className} ${errorClasses}`}
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
                                        inputMode="numeric"
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
