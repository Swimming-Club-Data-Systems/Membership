import React, { ReactNode } from "react";
import { Field } from "@/Utils/Form/Field";
import TextInput from "@/Components/Form/TextInput";
import DecimalInput from "@/Components/Form/DecimalInput";
import Select from "@/Components/Form/Select";
import Checkbox from "@/Components/Form/Checkbox";
import TextArea from "@/Components/Form/TextArea";

type FieldsArray = Field[];

const P = ({ children }) => (
    <div className="prose prose-sm">
        <p>{children}</p>
    </div>
);

const generateFields = (
    fields: FieldsArray,
    namePrefix?: string
): ReactNode[] => {
    // Take an array of fields and recursively generate TSX/JSX
    if (fields) return fields.map((field) => generateTsx(field, namePrefix));
    return null;
};

const generateTsx = (field: Field, namePrefix?: string): ReactNode => {
    const Tag = getComponent(field.type);

    const { initialValue, type, name, ...props } = field;

    // Prefix name if required
    const newName = namePrefix ? `${namePrefix}.${name}` : name;

    // We ignore errors here because the user is responsible for supplying props
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const Field = <Tag name={newName} {...props} />;

    return (
        <div className="col-start-1 col-end-13 md:col-start-1 md:col-end-8">
            {Field}
        </div>
    );
};

const getComponent = (name: string) => {
    switch (name) {
        case "textbox":
            return TextInput;
        case "textarea":
            return TextArea;
        case "numeric":
            return DecimalInput;
        case "select":
            return Select;
        case "checkbox":
            return Checkbox;
        case "p":
            return P;
    }
};

export default generateFields;
