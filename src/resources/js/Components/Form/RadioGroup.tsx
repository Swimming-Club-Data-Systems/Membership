import React, { createContext, ReactNode } from "react";
import { ErrorMessage } from "formik";

interface RadioGroupContextType {
    name?: string;
}
export const RadioGroupContext = createContext<RadioGroupContextType>({});

interface Props {
    label: string;
    name?: string;
    children: ReactNode;
    subtext?: string;
    legend?: string;
}

export const RadioGroup = ({
    label,
    name,
    subtext,
    legend,
    children,
}: Props) => {
    return (
        <RadioGroupContext.Provider value={{ name: name }}>
            <div>
                <label className="text-base font-semibold text-gray-900">
                    {label}
                </label>
                {subtext && <p className="text-sm text-gray-500">{subtext}</p>}
                <fieldset className="mt-4">
                    <legend className="sr-only">{legend || label}</legend>
                    <div className="space-y-4">{children}</div>
                </fieldset>

                {name && (
                    <ErrorMessage
                        name={name}
                        render={(message) => (
                            <p className="mt-2 mb-3 text-sm text-red-600">
                                {message}
                            </p>
                        )}
                    />
                )}
            </div>
        </RadioGroupContext.Provider>
    );
};

export default RadioGroup;
