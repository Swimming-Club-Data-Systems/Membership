import * as yup from "yup";
import { Field } from "@/Utils/Form/Field";

const generateYup = (field: Field) => {
    let rule;
    switch (field.type) {
        case "checkbox":
            rule = yup.boolean();
            break;
        case "numeric":
            rule = yup.number();
            break;
        case "select":
        case "textbox":
        case "textarea":
        default:
            rule = yup.string().nullable();
            break;
    }

    if (field.required && rule.required) {
        rule = rule.required("Field is required.");
    }

    if (field.type === "select" && field.items) {
        const values = field.items.map((item) => item.value);
        const names = field.items.map((item) => item.name);

        // Create oneOf
        rule = rule.oneOf(values, `Value must be one of: ${names.join(", ")}.`);
    }

    return rule;
};

const generateYupFields = (fields: Field[]) => {
    const yupFields = {};

    fields.forEach((field) => {
        if (field.name) {
            yupFields[field.name] = generateYup(field);
        }
    });

    return yupFields;
};

export default generateYupFields;
