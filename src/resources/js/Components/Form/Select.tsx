import React, { ReactNode, useCallback, useContext } from "react";
import { useField, useFormikContext } from "formik";
import {
    Select as BaseSelect,
    Props as BaseSelectProps,
} from "@/Components/Form/base/select/Select";
import { FormSpecialContext } from "@/Components/Form/Form";

interface Props extends Pick<BaseSelectProps, "items"> {
    id?: string;
    readOnly?: boolean;
    disabled?: boolean;
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
    leftText,
    rightButton,
    // rightText,
    className = "",
    keyField = "value",
    items = [],
    name,
    nullable,
    ...props
}) => {
    const [{ onChange, ...field }, meta, { setValue }] = useField(
        props.id || name
    );
    const { isSubmitting } = useFormikContext();
    const { formName, readOnly, removeDefaultInputMargin, ...context } =
        useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId = (formName ? formName + "_" : "") + (props.id || name);
    const marginBotton = mb || removeDefaultInputMargin ? "" : "mb-6";

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
                id={controlId}
                keyField={keyField}
                label={label}
                name={name}
                disabled={
                    isSubmitting ||
                    disabled ||
                    context.disabled ||
                    readOnly ||
                    props.readOnly
                }
                nullable={nullable}
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
