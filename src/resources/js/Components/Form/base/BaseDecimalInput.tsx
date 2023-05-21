import React, { useCallback, useEffect, useRef, useState } from "react";
import Input, { InputProps } from "@/Components/Form/base/Input";
import invariant from "invariant";

function usePrevious<T>(value: T): T | undefined {
    const ref = useRef<T>();
    useEffect(() => {
        ref.current = value;
    });
    return ref.current;
}

interface BaseDecimalInputProps
    extends Omit<InputProps, "onChange" | "onBlur"> {
    onChange: (ev: CustomEvent) => void;
    onBlur: (ev: CustomEvent) => void;
    /** The default value of the input if it's meant to be used as an uncontrolled component */
    defaultValue?: string;
    /** Allow an empty value instead of defaulting to 0.00 */
    allowEmptyValue?: boolean;
    /** The decimal precision of the value in the input */
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
}

export interface CustomEvent {
    target: {
        name?: string;
        id?: string;
        value: {
            formattedValue: string;
            rawValue: string;
        };
    };
}

const BaseDecimalInput: React.FC<BaseDecimalInputProps> = React.forwardRef(
    (
        {
            readOnly,
            disabled,
            className,
            name,
            id,
            type,
            inputMode,
            precision,
            onChange,
            onBlur,
            onKeyDown,
            value,
            defaultValue,
            allowEmptyValue,
            inputRef,
            ...props
        }: BaseDecimalInputProps,
        ref: React.ForwardedRef<HTMLInputElement>
    ) => {
        const emptyValue = allowEmptyValue ? "" : "0.00";

        const getSafeValueProp = useCallback(
            (initialValue) => {
                // We're intentionally preventing the use of number values to help prevent any unintentional rounding issues
                invariant(
                    typeof initialValue === "string",
                    "Decimal `value` prop must be a string"
                );

                if (initialValue && !allowEmptyValue) {
                    invariant(
                        initialValue !== "",
                        "Decimal `value` must not be an empty string. Please use `allowEmptyValue` or `0.00`"
                    );
                }
                return initialValue;
            },
            [allowEmptyValue]
        );

        const getSeparator = useCallback((separatorType) => {
            const numberWithGroupAndDecimalSeparator = 10000.1;
            return Intl.NumberFormat("en-GB")
                .formatToParts(numberWithGroupAndDecimalSeparator)
                .find((part) => part.type === separatorType)?.value;
        }, []);

        const isNaN = useCallback((valueToTest) => {
            return Number.isNaN(Number(valueToTest));
        }, []);

        /**
         * Format a user defined value
         */
        const formatValue = useCallback(
            (valueToFormat) => {
                if (isNaN(valueToFormat)) {
                    return valueToFormat;
                }

                /* Guards against any white-space only strings like "   " being
               mishandled and returned as `NaN` for the value displayed in the textbox */
                if (valueToFormat === "" || valueToFormat.match(/\s+/g)) {
                    return valueToFormat;
                }

                const separator = getSeparator("decimal");
                const [integer, remainder] = valueToFormat.split(".");

                const formattedInteger = Intl.NumberFormat("en-GB", {
                    maximumFractionDigits: 0,
                }).format(integer);

                let formattedNumber = formattedInteger;
                if (remainder?.length > precision) {
                    formattedNumber += `${separator + remainder}`;
                } else if (remainder?.length <= precision) {
                    formattedNumber += `${
                        separator +
                        remainder +
                        "0".repeat(precision - remainder.length)
                    }`;
                } else {
                    formattedNumber += `${
                        precision ? separator + "0".repeat(precision) : ""
                    }`;
                }
                return formattedNumber;
            },
            [getSeparator, isNaN, precision]
        );

        /**
         * Determine if the precision value has changed from the previous ref value for precision
         */
        const prevPrecisionValue = usePrevious(precision);

        useEffect(() => {
            if (prevPrecisionValue && prevPrecisionValue !== precision) {
                // eslint-disable-next-line no-console
                console.error(
                    "Decimal `precision` prop has changed value. Changing the Decimal `precision` prop has no effect."
                );
            }
        }, [precision, prevPrecisionValue]);

        const removeDelimiters = useCallback(
            (valueToFormat) => {
                const delimiterMatcher = new RegExp(
                    `[\\${getSeparator("group")} ]*`,
                    "g"
                );
                return valueToFormat.replace(delimiterMatcher, "");
            },
            [getSeparator]
        );

        /**
         * Convert raw input to a standard decimal format
         */
        const toStandardDecimal = useCallback(
            (i18nValue) => {
                const valueWithoutNBS =
                    getSeparator("group")?.match(/\s+/) &&
                    !i18nValue.match(/\s{2,}/)
                        ? i18nValue.replace(/\s+/g, "")
                        : i18nValue;
                /* If a value is passed in that is a number but has too many delimiters in succession, we want to handle this
            value without formatting it or removing delimiters. We also want to consider that,
            if a value consists of only delimiters, we want to treat that
            value in the same way as if the value was NaN. We want to pass this value to the
            formatValue function, before the delimiters can be removed. */
                const errorsWithDelimiter = new RegExp(
                    `([^A-Za-z0-9]{2,})|(^[^A-Za-z0-9-]+)|([^0-9a-z-,.])|([^0-9-,.]+)|([W,.])$`,
                    "g"
                );
                const separator = getSeparator("decimal") as string;
                const separatorRegex = new RegExp(
                    separator === "." ? `\\${separator}` : separator,
                    "g"
                );
                if (
                    valueWithoutNBS.match(errorsWithDelimiter) ||
                    (valueWithoutNBS.match(separatorRegex) || []).length > 1
                ) {
                    return valueWithoutNBS;
                }

                const withoutDelimiters = removeDelimiters(valueWithoutNBS);
                return withoutDelimiters.replace(
                    new RegExp(`\\${separator}`, "g"),
                    "."
                );
            },
            [getSeparator, removeDelimiters]
        );

        const decimalValue = getSafeValueProp(
            defaultValue || value || emptyValue
        );
        const [stateValue, setStateValue] = useState(
            isNaN(toStandardDecimal(decimalValue))
                ? decimalValue
                : formatValue(decimalValue)
        );

        const createEvent = (formatted: string, raw?: string): CustomEvent => {
            return {
                target: {
                    name,
                    id,
                    value: {
                        formattedValue: formatValue(
                            toStandardDecimal(formatted)
                        ),
                        rawValue: raw || toStandardDecimal(formatted),
                    },
                },
            };
        };

        const handleOnChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
            const { value: val } = ev.target;
            setStateValue(val);
            if (onChange) onChange(createEvent(val));
            console.log(createEvent(val));
        };

        const handleOnBlur = (ev: React.FocusEvent<HTMLInputElement>) => {
            const { value: updatedValue } = ev.target;
            let event;

            if (updatedValue) {
                const standardVisible = toStandardDecimal(updatedValue);
                const formattedValue = isNaN(standardVisible)
                    ? updatedValue
                    : formatValue(standardVisible);

                event = createEvent(formattedValue, standardVisible);
                setStateValue(formattedValue);
            } else {
                event = createEvent(emptyValue);
                setStateValue(emptyValue);
            }

            if (onBlur) onBlur(event);
        };

        const isControlled = value !== undefined;
        const prevControlledRef = useRef<boolean>();

        const prevValue = usePrevious(value);

        useEffect(() => {
            const standardDecimalValue = toStandardDecimal(stateValue);

            if (isControlled) {
                const valueProp = getSafeValueProp(value);
                if (standardDecimalValue !== valueProp) {
                    if (valueProp === "" && prevValue === "") {
                        setStateValue(formatValue(emptyValue));
                    } else {
                        setStateValue(formatValue(valueProp));
                    }
                }
            }
        }, [
            emptyValue,
            formatValue,
            getSafeValueProp,
            isControlled,
            prevValue,
            stateValue,
            toStandardDecimal,
            value,
        ]);

        return (
            <Input
                readOnly={readOnly}
                disabled={disabled}
                className={className}
                id={id}
                name={name}
                type={type}
                inputMode={inputMode}
                onChange={handleOnChange}
                onBlur={handleOnBlur}
                value={stateValue}
                data-component="decimal"
                ref={ref}
                inputRef={inputRef}
                {...props}
            />
        );
    }
);

BaseDecimalInput.displayName = "BaseDecimalInput";

export default BaseDecimalInput;
