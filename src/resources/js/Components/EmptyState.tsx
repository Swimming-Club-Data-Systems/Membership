import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";
import React, { ReactNode } from "react";

type EmptyStateProps = {
    /** Override the title, which by default says `No results` */
    title?: string;
    children: ReactNode;
};

const EmptyState = (props: EmptyStateProps) => {
    return (
        <>
            <div className="overflow-hidden bg-white px-4 pt-5 pb-4 shadow sm:p-6 sm:pb-4 lg:rounded-lg">
                <div className="sm:flex sm:items-start">
                    <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <ExclamationTriangleIcon
                            className="h-6 w-6 text-red-600"
                            aria-hidden="true"
                        />
                    </div>
                    <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 className="text-lg font-medium leading-6 text-gray-900">
                            {props.title || "No results"}
                        </h3>
                        <div className="mt-2 text-sm text-gray-500">
                            {props.children}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default EmptyState;
