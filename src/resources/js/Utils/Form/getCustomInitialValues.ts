import { Field } from "@/Utils/Form/Field";

type FieldsArray = Field[];

const getCustomInitialValues = (
    fields: FieldsArray
): { [key: string]: string | number | null } => {
    const initialValues = {};

    if (fields) {
        fields.forEach((field) => {
            if (field.name) {
                if (field.initialValue) {
                    initialValues[field.name] = field.initialValue;
                } else {
                    initialValues[field.name] = null;
                }
            }
        });
    }

    return initialValues;
};

export default getCustomInitialValues;
