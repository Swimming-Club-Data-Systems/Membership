import React, { ReactElement, ReactNode } from "react";

type Props = {
    /** The title of the action panel */
    title: string;
    /** The message associated with the action panel */
    children: ReactNode;
    /** The buttons to take an action */
    buttons: ReactNode;
};

const ActionPanel = ({ title, children, buttons }: Props): ReactElement => {
    return (
        <div className="bg-white shadow sm:rounded-lg">
            <div className="px-4 py-5 sm:p-6">
                <h3 className="text-base font-semibold leading-6 text-gray-900">
                    {title}
                </h3>
                <div className="mt-2 max-w-xl text-sm text-gray-500">
                    {children}
                </div>
                <div className="mt-5 text-sm">{buttons}</div>
            </div>
        </div>
    );
};

export default ActionPanel;
