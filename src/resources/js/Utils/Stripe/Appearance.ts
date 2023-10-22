import { Appearance } from "@stripe/stripe-js";

/**
 * The appearance
 */
export const appearance: Appearance = {
    theme: "stripe",
    variables: {
        fontSizeBase: "1rem",
        fontSizeXl: "1.25rem",
        fontSizeLg: "1.125rem",
        fontSizeSm: "0.875rem",
        fontSizeXs: "0.75rem",
        fontSize2Xs: "0.75rem",
        fontSize3Xs: "0.75rem",
        fontLineHeight: "1.25rem",
        //fontFeatureSettings: '"cv11", "ss01"',
        colorPrimary: "#4f46e5",
        colorBackground: "#ffffff",
        colorText: "#111827",
        colorDanger: "#7f1d1d",
        fontFamily:
            '"Inter var", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
        spacingUnit: "4px",
        spacingGridColumn: "1rem",
        spacingGridRow: "1rem",
        borderRadius: "0.5rem",
        // See all possible variables below
    },
    rules: {
        ".Accordion": {
            boxShadow:
                "rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.1) 0px 1px 2px -1px",
        },
        ".AccordionItem": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Action": {
            fontSize: "var(--fontSizeSm)",
        },
        ".BlockAction": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Button": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Checkbox": {
            fontSize: "var(--fontSizeSm)",
        },
        ".CheckboxLabel": {
            fontSize: "var(--fontSizeSm)",
        },
        ".CodeInput": {
            fontSize: "var(--fontSizeSm)",
        },
        ".DropdownItem": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Error": {
            fontSize: "var(--fontSizeSm)",
            color: "#dc2626",
            marginTop: "0.5rem",
        },
        ".Input": {
            fontSize: "var(--fontSizeSm)",
            paddingTop: "0.5rem",
            paddingRight: "0.75rem",
            paddingBottom: "0.5rem",
            paddingLeft: "0.75rem",
            borderRadius: "0.375rem",
            lineHeight: "1.25rem",
            borderColor: "#d1d5db",
            boxShadow:
                "rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0) 0px 0px 0px 0px, rgba(0, 0, 0, 0.05) 0px 1px 2px 0px",
        },
        ".Input:focus": {
            borderColor: "var(--colorPrimary)",
            outline: "1px solid var(--colorPrimary)",
            outlineColor: "var(--colorPrimary)",
            boxShadow: "none",
        },
        ".Input--invalid": {
            borderColor: "#ef4444",
            boxShadow: "none",
            // outline: "1px solid #ef4444",
            // outlineColor: "#ef4444",
        },
        ".Input--empty": {
            // borderColor: "#ef4444",
            // outline: "1px solid #ef4444",
            // outlineColor: "#ef4444",
        },
        ".Input--invalid:focus": {
            borderColor: "#ef4444",
            outline: "1px solid #ef4444",
            outlineColor: "#ef4444",
        },
        ".Input--empty:focus": {
            // borderColor: "#ef4444",
            // outline: "1px solid #ef4444",
            // outlineColor: "#ef4444",
        },
        ".Label": {
            fontSize: "var(--fontSizeSm)",
            fontWeight: "500",
            color: "#374151",
            marginBottom: "0.25rem",
        },
        ".Link": {
            fontSize: "var(--fontSizeSm)",
        },
        ".MenuAction": {
            fontSize: "var(--fontSizeSm)",
        },
        ".PickerAction": {
            fontSize: "var(--fontSizeSm)",
        },
        ".PickerItem": {
            fontSize: "var(--fontSizeSm)",
        },
        ".RedirectText": {
            fontSize: "var(--fontSizeSm)",
        },
        ".SecondaryLink": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Switch": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Tab": {
            fontSize: "var(--fontSizeSm)",
        },
        ".TabLabel": {
            fontSize: "var(--fontSizeSm)",
        },
        ".TermsLink": {
            fontSize: "var(--fontSizeSm)",
        },
        ".TermsText": {
            fontSize: "var(--fontSizeSm)",
        },
        ".Text": {
            fontSize: "var(--fontSizeSm)",
        },
        // AccordionItem, Action, BlockAction, Button, Checkbox, CheckboxLabel, CodeInput, DropdownItem, Error, Input, Label, Link, MenuAction, PickerAction, PickerItem, RedirectText, SecondaryLink, Switch, Tab, TabLabel, TermsLink, TermsText, Text
        ".p-AccordionButton": {
            fontSize: "var(--fontSizeSm)",
        },
        ".p-AccordionHeader": {
            fontSize: "var(--fontSizeSm)",
        },
    },
};
