import React, { ReactNode, useCallback, useContext } from "react";
import { useField, useFormikContext } from "formik";
import BaseInput from "./BaseInput";
import { FormSpecialContext } from "@/Components/Form/Form";
import BaseDecimalInput, {
    CustomEvent,
} from "@/Components/Form/base/BaseDecimalInput";

type Props = {
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
    precision?:
        | 0
        | 1
        | 2
        | 3
        | 4
        | 5
        | 6
        | 7
        | 8
        | 9
        | 10
        | 11
        | 12
        | 13
        | 14
        | 15;
};

const DecimalInput: React.FC<Props> = ({
    label,
    disabled,
    type,
    leftText,
    rightButton,
    // rightText,
    className = "",
    precision = 0,
    ...props
}) => {
    const [{ onChange, onBlur, value, ...field }, meta] = useField(props);
    const { isSubmitting, setFieldValue, setFieldTouched, validateField } =
        useFormikContext();
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);

    const getInputValue = (e) => {
        return e.target.value.rawValue !== undefined
            ? e.target.value.rawValue
            : e.target.value;
    };

    const onChangeInternal = useCallback((ev: CustomEvent) => {
        setFieldValue(props.name, getInputValue(ev));
    }, []);

    const onBlurInternal = useCallback((ev) => {
        setFieldValue(props.name, getInputValue(ev));
        setTimeout(() => validateField(props.name), 0);
        setFieldTouched(props.name);
    }, []);

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
                <BaseDecimalInput
                    readOnly={readOnly}
                    disabled={isSubmitting || disabled || context.disabled}
                    className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-gray-100 disabled:bg-gray-100 text-right ${className} ${errorClasses}`}
                    id={controlId}
                    type={type}
                    precision={precision}
                    {...field}
                    {...props}
                    onChange={onChangeInternal}
                    onBlur={onBlurInternal}
                    inputMode="decimal"
                    value={value.toString() || ""}
                />
            }
            rightButton={rightButton}
            {...props}
        />
    );
};

export default DecimalInput;
