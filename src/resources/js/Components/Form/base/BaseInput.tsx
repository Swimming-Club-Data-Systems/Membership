import React, { ReactNode, useContext } from "react";
import { ExclamationCircleIcon } from "@heroicons/react/24/solid";
import { FormSpecialContext } from "../Form";

type Props = {
    id?: string;
    name: string;
    showValid?: boolean;
    label: string;
    help?: string;
    mb?: string;
    disabled?: boolean;
    type?: string;
    leftText?: string;
    leftSelect?: null;
    rightButton?: ReactNode;
    startIcon?: ReactNode;
    endIcon?: ReactNode;
    cornerHint?: string;
    className?: string;
    input: ReactNode;
    maxLength?: number;
    isValid?: boolean;
    isInvalid?: boolean;
    length?: number;
    error?: string;
};

export const BaseInput: React.FC<Props> = ({
    label,
    help,
    mb,
    disabled,
    type,
    leftText,
    leftSelect,
    rightButton,
    // rightText,
    startIcon,
    endIcon,
    cornerHint,
    className = "",
    input,
    maxLength,
    isValid,
    isInvalid,
    length,
    error,
    ...props
}) => {
    const formSpecialContext = useContext(FormSpecialContext);
    const marginBotton =
        mb || formSpecialContext.removeDefaultInputMargin ? "" : "mb-6";
    const controlId =
        (formSpecialContext.formName ? formSpecialContext.formName + "_" : "") +
        (props.id || props.name);

    if (!type) {
        type = "text";
    }

    const textColour = isInvalid ? "text-red-600" : "text-gray-500";

    if (!leftText) {
        className += " rounded-l-md ";
    }

    // if (!rightText) {
    //     className += " rounded-r-md ";
    // }

    return (
        <>
            <div className={marginBotton}>
                <div className="flex justify-between">
                    <label
                        htmlFor={controlId}
                        className="block text-sm font-medium text-gray-700"
                    >
                        {label}
                    </label>
                    {cornerHint && (
                        <span className="text-sm text-gray-500">
                            {cornerHint}
                        </span>
                    )}
                </div>

                <div className="relative mt-1 flex rounded-md shadow-sm focus-within:z-10">
                    {leftText && !leftSelect && (
                        <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            {leftText}
                        </span>
                    )}
                    {leftSelect && !leftText && (
                        <div className="absolute inset-y-0 left-0 flex items-center">
                            {leftSelect}
                        </div>
                    )}
                    {startIcon && (
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            startIcon
                        </div>
                    )}
                    {input}
                    {endIcon && (
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            endIcon
                        </div>
                    )}
                    {isInvalid && (
                        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <ExclamationCircleIcon
                                className="h-5 w-5 text-red-500"
                                aria-hidden="true"
                            />
                        </div>
                    )}
                    {/* Right text is not supported right now */}
                    {/* {rightText && (
                        <span className="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            {rightText}
                        </span>
                    )} */}
                    {rightButton}
                </div>

                <div className="flex justify-between">
                    <div>
                        {isInvalid && (
                            <p className="mt-2 text-sm text-red-600">{error}</p>
                        )}

                        {help && (
                            <p className="mt-2 text-sm text-gray-500">{help}</p>
                        )}
                    </div>

                    {maxLength && (
                        <p className={`mt-2 text-sm ${textColour}`}>
                            {length}/{maxLength}
                        </p>
                    )}
                </div>
            </div>
        </>
    );
};

export default BaseInput;
