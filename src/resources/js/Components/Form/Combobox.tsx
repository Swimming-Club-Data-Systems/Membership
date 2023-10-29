import React, { ReactNode, useContext } from "react";
import { useField, useFormikContext } from "formik";
import { Combobox as BaseCombobox } from "@/Components/Form/base/combobox/Combobox";
import { FormSpecialContext } from "@/Components/Form/Form";

interface Props {
    id?: string;
    disabled?: boolean;
    readOnly?: boolean;
    leftText?: string;
    rightButton?: ReactNode;
    className?: string;
    name: string;
    label: string;
    autoComplete?: string;
    endpoint: string;
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
    keyField = "id",
    ...props
}) => {
    const [{ onChange, ...field }, meta, helpers] = useField(props);
    const { isSubmitting } = useFormikContext();
    const { formName, readOnly, ...context } = useContext(FormSpecialContext);
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId =
        (formName ? formName + "_" : "") + (props.id || props.name);
    const formSpecialContext = useContext(FormSpecialContext);
    const marginBotton =
        mb || formSpecialContext.removeDefaultInputMargin ? "" : "mb-6";

    if (!leftText) {
        className += " rounded-l-md ";
    }

    if (!rightButton) {
        className += " rounded-r-md ";
    }

    const changeHandler = (value) => {
        helpers.setValue(value[keyField]);
    };

    return (
        <div className={marginBotton}>
            <BaseCombobox
                id={controlId}
                keyField={keyField}
                label={label}
                name={props.name}
                disabled={
                    isSubmitting ||
                    disabled ||
                    context.disabled ||
                    props.readOnly ||
                    readOnly
                }
                {...field}
                onChange={changeHandler}
                className={className}
                isInvalid={isInvalid}
                {...props}
            />
        </div>
    );
};

export default Combobox;
