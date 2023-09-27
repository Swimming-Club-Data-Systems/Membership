import React, { ReactNode, useCallback, useContext } from "react";
import { useField, useFormikContext } from "formik";
import {
    Select as BaseSelect,
    Props as BaseSelectProps,
} from "@/Components/Form/base/select/Select";
import { FormSpecialContext } from "@/Components/Form/Form";

interface Props extends Pick<BaseSelectProps, "items"> {
    id?: string;
    disabled?: boolean;
    type?: string;
    leftText?: string;
    rightButton?: ReactNode;
    className?: string;
    name: string;
    label: string;
    autoComplete?: string;
    keyField?: string;
    help?: string;
    nullable?: boolean;
    mb?: string;
}

const Combobox: React.FC<Props> = ({
    label,
    disabled,
    mb,
    type,
    leftText,
    rightButton,
    // rightText,
    className = "",
    keyField = "value",
    items,
    ...props
}) => {
    const [{ onChange, ...field }, meta, { setValue, ...helpers }] =
        useField(props);
    const { isSubmitting } = useFormikContext();
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);
    const formSpecialContext = useContext(FormSpecialContext);
    const marginBotton =
        mb || formSpecialContext.removeDefaultInputMargin ? "" : "mb-6";

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

    const changeHandler = useCallback(
        (value) => {
            setValue(value[keyField]);
        },
        [setValue, keyField]
    );

    return (
        <div className={marginBotton}>
            <BaseSelect
                keyField={keyField}
                label={label}
                name={name}
                disabled={
                    isSubmitting || disabled || context.disabled || readOnly
                }
                {...field}
                onChange={changeHandler}
                className={className}
                isInvalid={isInvalid}
                items={items}
                // input={
                //     <input
                //         readOnly={readOnly}
                //         disabled={isSubmitting || disabled || context.disabled}
                //         className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                //         id={controlId}
                //         type={type}
                //         {...field}
                //         {...props}
                //     />
                // }
                // rightButton={rightButton}
                {...props}
            />
        </div>
    );
};

export default Combobox;
