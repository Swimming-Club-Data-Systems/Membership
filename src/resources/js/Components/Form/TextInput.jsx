import React from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";

const TextInput = ({
    disabled,
    type,
    leftText,
    // rightText,
    className = "",
    ...props
}) => {
    const [field, meta] = useField(props);
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
                <input
                    disabled={isSubmitting || disabled}
                    className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                    id={controlId}
                    type={type}
                    {...field}
                    {...props}
                />
            }
            {...props}
        />
    );
};

export default TextInput;
