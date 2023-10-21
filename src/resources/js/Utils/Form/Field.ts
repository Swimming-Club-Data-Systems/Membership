export type Field = {
    name?: string;
    initialValue?: string | number;
    type?: "textbox" | "textarea" | "numeric" | "select" | "checkbox" | "p";
    required?: boolean;
    items?: SelectOptions;
    [key: string]: string | number | boolean | null | SelectOptions;
};

type SelectOption = { value: string; name: string };
type SelectOptions = SelectOption[];
