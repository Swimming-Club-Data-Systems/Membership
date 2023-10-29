import React from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";

const NativeSelect = ({
    options = [],
    disabled,
    type,
    leftText,
    // rightText,
    className = "",
    ...props
}) => {
    const [field, meta] = useField({ ...props, type: "select" });
    const { isSubmitting } = useFormikContext();
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId = props.id || props.name;

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

    return (
        <BaseInput
            input={
                <>
                    <select
                        disabled={isSubmitting || disabled}
                        name="location"
                        className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                        // className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        id={controlId}
                        type={type}
                        {...field}
                        {...props}
                    >
                        {options.map((option) => {
                            return (
                                <option
                                    key={option.key}
                                    value={option.key}
                                    disabled={option.disabled}
                                >
                                    {option.name}
                                </option>
                            );
                        })}
                    </select>
                </>
            }
            {...props}
        />
    );
};

export default Select;
