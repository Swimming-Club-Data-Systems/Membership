import React, { ReactNode, useContext } from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import Input, { InputProps } from "@/Components/Form/base/Input";

interface Props extends InputProps {
    disabled?: boolean;
    type?: string;
    leftText?: string;
    rightButton?: ReactNode;
    className?: string;
    id?: string;
    name: string;
    label: string;
    autoComplete?: string;
    help?: string;
}

const TextInput = React.forwardRef<HTMLInputElement, Props>(
    (
        {
            label,
            disabled,
            type,
            leftText,
            rightButton,
            // rightText,
            className = "",
            ...props
        }: Props,
        ref
    ): JSX.Element => {
        const [{ onBlur, ...field }, meta] = useField(props);
        const { isSubmitting } = useFormikContext();
        const { formName, readOnly, ...context } =
            useContext(FormSpecialContext);
        // const isValid = props.showValid && meta.touched && !meta.error;
        const isInvalid = meta.touched && meta.error;
        const controlId =
            (formName ? formName + "_" : "") + (props.id || props.name);

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
                label={label}
                type={type}
                input={
                    <Input
                        readOnly={readOnly}
                        disabled={isSubmitting || disabled || context.disabled}
                        className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                        id={controlId}
                        type={type}
                        onBlur={(ev) => {
                            onBlur(ev);
                            if (props.onBlur) {
                                props.onBlur(ev);
                            }
                        }}
                        {...field}
                        {...props}
                        ref={ref}
                    />
                }
                rightButton={rightButton}
                {...props}
            />
        );
    }
);

TextInput.displayName = "TextInput";

export default TextInput;
