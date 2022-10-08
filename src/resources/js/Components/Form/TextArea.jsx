import React from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";

const TextArea = ({
    disabled,
    type,
    leftText,
    rightButton,
    // rightText,
    className = "",
    rows = 4,
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

    if (!rightButton) {
        className += " rounded-r-md ";
    }

    return (
        <BaseInput
            input={
                <textarea
                    disabled={isSubmitting || disabled}
                    className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                    id={controlId}
                    type={type}
                    rows={rows}
                    {...field}
                    {...props}
                />
            }
            rightButton={rightButton}
            {...props}
        />
    );
};

export default TextArea;
