import React, { useContext, useState } from "react";
import { useField, useFormikContext } from "formik";
import { v4 } from "uuid";
import { FormSpecialContext } from "@/Components/Form/Form";
import { bool } from "yup";

export interface RadioCheckProps {
    name: string;
    type: string;
    label: string;
    disabled?: boolean;
    readOnly?: boolean;
    help?: string;
    mb?: string;
    /** Set when used inside a RadioGroup so that error messages appear on the group */
    inContext?: boolean;
}

const RadioCheck = ({
    type,
    label,
    mb,
    disabled: propsDisabled,
    inContext,
    ...props
}: RadioCheckProps) => {
    const marginBotton = mb || "mb-3";

    const [field, meta] = useField({
        type,
        ...props,
    });
    const { readOnly: contextReadOnly, disabled: contextDisabled } =
        useContext(FormSpecialContext);
    const { isSubmitting, ...context } = useFormikContext();

    let feedback = null;
    let feedbackType = null;
    if (meta.touched && meta.error) {
        feedback = meta.error;
        feedbackType = "invalid";
    }

    const disabled = propsDisabled || contextDisabled;
    const readOnly = contextReadOnly;

    const [id] = useState(v4());

    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;

    let checkStyles = "";
    if (type === "checkbox") {
        checkStyles = "rounded";
    }

    let invalidCheckStyles = "";
    if (isInvalid) {
        invalidCheckStyles = "text-red-600 focus:ring-red-500";
    }

    return (
        <div className={`flex items-start ${marginBotton}`}>
            <div className="flex h-5 items-center">
                <input
                    id={id}
                    {...field}
                    {...props}
                    readOnly={readOnly}
                    disabled={isSubmitting || disabled}
                    type={type}
                    className={`h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 ${checkStyles} ${invalidCheckStyles}`}
                />
            </div>
            <div className="ml-3 text-sm">
                <label
                    htmlFor={id}
                    className="font-medium text-gray-700 select-none flex break-words"
                >
                    {label}
                </label>

                {!inContext && isInvalid && (
                    <p className="mt-2 text-sm text-red-600">{meta.error}</p>
                )}

                {props.help && <p className="text-gray-500">{props.help}</p>}
            </div>
        </div>
    );
};

export default RadioCheck;
