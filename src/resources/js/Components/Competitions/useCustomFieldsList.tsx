import { useMemo } from "react";

export type CustomField = {
    friendly_name: string;
    friendly_value?: string;
    name: string;
    value?: string;
};

export type CustomFields = CustomField[];

export type Filter = "show_in_preview";

const useCustomFieldsList = (custom_fields: CustomFields, filter?: Filter) => {
    return useMemo(
        () =>
            custom_fields
                .filter((field) => {
                    if (filter) {
                        return !!field[filter];
                    } else {
                        return true;
                    }
                })
                .map((field) => {
                    return {
                        key: `custom_field_${field.name}`,
                        term: field.friendly_name,
                        definition: field.friendly_value
                            ? field.friendly_value
                            : "Not given",
                    };
                }),
        [filter, custom_fields]
    );
};

export default useCustomFieldsList;
