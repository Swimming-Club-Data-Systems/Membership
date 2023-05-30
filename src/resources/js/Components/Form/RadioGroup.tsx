import React, { ReactNode } from "react";

interface Props {
    label: string;
    children: ReactNode;
    subtext?: string;
    legend?: string;
}

export const RadioGroup = ({ label, subtext, legend, children }: Props) => {
    return (
        <div>
            <label className="text-base font-semibold text-gray-900">
                {label}
            </label>
            {subtext && <p className="text-sm text-gray-500">{subtext}</p>}
            <fieldset className="mt-4">
                <legend className="sr-only">{legend || label}</legend>
                <div className="space-y-4">{children}</div>
            </fieldset>
        </div>
    );
};

export default RadioGroup;
