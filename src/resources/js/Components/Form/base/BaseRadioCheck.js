import React, { useState } from "react";
import { v4 } from "uuid";

const BaseRadioCheck = ({ type, label, mb, disabled, error, ...props }) => {
    const marginBotton = mb || "mb-3";

    const [id] = useState(v4());

    let checkStyles = "";
    if (type === "checkbox") {
        checkStyles = "rounded";
    }

    return (
        <div className={`flex items-start ${marginBotton}`}>
            <div className="flex h-5 items-center">
                <input
                    id={id}
                    {...props}
                    disabled={disabled}
                    type={type}
                    className={`h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 ${checkStyles}`}
                />
            </div>
            <div className="ml-3 text-sm">
                <label htmlFor={id} className="font-medium text-gray-700">
                    {label}
                </label>
                {props.help && <p className="text-gray-500">{props.help}</p>}
            </div>
        </div>
    );
};

export default BaseRadioCheck;
