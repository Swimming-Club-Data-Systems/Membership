import React from "react";
import { ErrorMessage } from "formik";
import { ExclamationCircleIcon } from "@heroicons/react/24/solid";

type ArrayErrorMessageProps = {
    name: string;
};

const ArrayErrorMessage: React.FC<ArrayErrorMessageProps> = ({ name }) => {
    return (
        <ErrorMessage
            name={name}
            render={(errors) => {
                if (!Array.isArray(errors)) {
                    return (
                        <div className="text-sm text-red-600">
                            <ExclamationCircleIcon
                                className="inline-block h-5 w-5 text-red-500"
                                aria-hidden="true"
                            />{" "}
                            {errors}
                        </div>
                    );
                }
            }}
        />
    );
};

export default ArrayErrorMessage;
