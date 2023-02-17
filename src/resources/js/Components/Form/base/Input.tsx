import React, { useEffect, useRef } from "react";

export interface CommonInputProps
    extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "type"> {
    /* The default value alignment on the input */
    align?: "right" | "left";
    /** Override the variant component */
    as?: React.ElementType;
    /** If true the Component will be focused when rendered */
    autoFocus?: boolean;
    /** If true, the component will be disabled */
    disabled?: boolean;
    /** HTML id attribute of the input */
    id?: string;
    /** A callback to retrieve the input reference */
    inputRef?: (input: React.RefObject<HTMLInputElement>) => void;
    /** Name of the input */
    name?: string;
    /** Specify a callback triggered on blur */
    onBlur?: (ev: React.FocusEvent<HTMLInputElement>) => void;
    /** Specify a callback triggered on change */
    onChange?: (ev: React.ChangeEvent<HTMLInputElement>) => void;
    /** pecify a callback triggered on click */
    onClick?: (ev: React.MouseEvent<HTMLInputElement>) => void;
    /** Specify a callback triggered on focus */
    onFocus?: (ev: React.FocusEvent<HTMLInputElement>) => void;
    /** Specify a callback triggered on keyDown */
    onKeyDown?: (ev: React.KeyboardEvent<HTMLInputElement>) => void;
    /** Placeholder string to be displayed in input */
    placeholder?: string;
    /** If true, the component will be read-only */
    readOnly?: boolean;
    /** Flag to configure component as mandatory */
    required?: boolean;
}

export interface InputProps extends CommonInputProps {
    /** The visible width of the text control, in average character widths */
    cols?: number;
    /** Integer to determine a timeout for the defered callback */
    deferTimeout?: number;
    /** Defered callback to be called after the onChange event */
    onChangeDeferred?: (ev: React.ChangeEvent<HTMLInputElement>) => void;
    /** The number of visible text lines for the control */
    rows?: number;
    /** HTML type attribute of the input */
    type?: string;
}

export function selectTextOnFocus(input: React.RefObject<HTMLInputElement>) {
    // setTimeout is required so the dom has a chance to place the cursor in the input
    setTimeout(() => {
        if (input?.current) {
            const { selectionStart, selectionEnd, value } = input.current;
            const { length } = value;
            // only select text if cursor is at the very end or the very start of the value
            if (
                (selectionStart === 0 && selectionEnd === 0) ||
                (selectionStart === length && selectionEnd === length)
            ) {
                if (document.activeElement === input.current) {
                    input.current.setSelectionRange(0, length);
                }
            }
        }
    });
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
    (
        {
            align,
            placeholder,
            disabled,
            readOnly,
            autoFocus,
            inputRef,
            onClick,
            onChangeDeferred,
            onChange,
            onBlur,
            onFocus,
            deferTimeout,
            type = "text",
            id,
            name,
            className,
            ...rest
        }: InputProps,
        ref
    ): JSX.Element => {
        const deferredTimeout = useRef<null | ReturnType<typeof setTimeout>>(
            null
        );
        let input = useRef<HTMLInputElement>(null);

        if (ref) {
            input = ref as React.MutableRefObject<HTMLInputElement>;
        }

        useEffect(() => {
            if (autoFocus && input.current) {
                input.current.focus();
            }
        }, [autoFocus, input]);

        useEffect(() => {
            if (inputRef) {
                inputRef(input);
            }
        }, [input, inputRef]);

        // useEffect(() => {
        //     if (context.inputRef) {
        //         context.inputRef(input);
        //     }
        // }, [context, input]);

        // useEffect(() => {
        //     if (disabled && context.onBlur) {
        //         context.onBlur();
        //     }
        // }, [disabled, context]);

        const handleClick = (ev: React.MouseEvent<HTMLInputElement>) => {
            if (onClick) {
                onClick(ev);
            }
            input?.current?.focus();
        };

        const handleFocus = (ev: React.FocusEvent<HTMLInputElement>) => {
            if (onFocus) {
                onFocus(ev);
            }
            // if (context.onFocus) {
            //     context.onFocus();
            // }
            // if (groupContext.onFocus) {
            //     groupContext.onFocus();
            // }
            // if (type === "text") {
            selectTextOnFocus(input);
            // }
        };

        const handleBlur = (ev: React.FocusEvent<HTMLInputElement>) => {
            if (onBlur) {
                onBlur(ev);
            }
            // if (context.onBlur) {
            //     context.onBlur();
            // }
            // if (groupContext.onBlur) {
            //     groupContext.onBlur();
            // }
        };

        const handleDeferred = (ev: React.ChangeEvent<HTMLInputElement>) => {
            if (onChangeDeferred) {
                if (deferredTimeout.current) {
                    clearTimeout(deferredTimeout.current);
                }
                deferredTimeout.current = setTimeout(() => {
                    onChangeDeferred(ev);
                }, deferTimeout || 750);
            }
        };

        const handleChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
            if (onChange) {
                onChange(ev);
            }
            handleDeferred(ev);
        };

        return (
            <input
                {...rest}
                // aria-labelledby={context.ariaLabelledBy}
                // align={align}
                placeholder={placeholder}
                disabled={disabled}
                readOnly={readOnly}
                name={name}
                type={type}
                id={id || name}
                ref={input}
                data-element="input"
                onFocus={handleFocus}
                onBlur={handleBlur}
                onClick={handleClick}
                onChange={handleChange}
                className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className}`}
            />
        );
    }
);

Input.displayName = "Input";

export default Input;
