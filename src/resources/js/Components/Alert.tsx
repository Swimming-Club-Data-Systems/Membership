import React, { ReactNode, useEffect, useRef, useState } from "react";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    XMarkIcon,
} from "@heroicons/react/24/solid";

type AlertListProps = {
    children: ReactNode;
};

export const AlertList: React.FC<AlertListProps> = ({ children }) => {
    return (
        <ul role="list" className="list-disc space-y-1 pl-5">
            {children}
        </ul>
    );
};

type Action = {
    id: string | number;
    onClick: (ev) => void;
    text: string;
};

type Props = {
    className?: string;
    variant?: "error" | "danger" | "warning" | "success" | "primary";
    handleDismiss?: (e) => void;
    title: string;
    actions?: Action[];
    children: ReactNode | string;
    dismissable?: boolean;
};

const Alert: React.FC<Props> = (props) => {
    let variantClass, symbol, titleClass, dismissColour;
    switch (props.variant) {
        case "error":
        case "danger":
            variantClass = "bg-red-50 text-red-700";
            titleClass = "text-red-800";
            dismissColour =
                "bg-red-50 text-red-500 hover:bg-red-100 focus:ring-offset-red-50 focus:ring-red-600";
            symbol = (
                <XCircleIcon
                    className="h-5 w-5 text-red-400"
                    aria-hidden="true"
                />
            );
            break;
        case "warning":
            variantClass = "bg-yellow-50 text-yellow-700";
            titleClass = "text-yellow-800";
            dismissColour =
                "bg-yellow-50 text-yellow-500 hover:bg-yellow-100 focus:ring-offset-yellow-50 focus:ring-yellow-600";
            symbol = (
                <ExclamationTriangleIcon
                    className="h-5 w-5 text-yellow-400"
                    aria-hidden="true"
                />
            );
            break;
        default:
            variantClass = "bg-green-50 text-green-700";
            titleClass = "text-green-800";
            dismissColour =
                "bg-green-50 text-green-500 hover:bg-green-100 focus:ring-offset-green-50 focus:ring-green-600";
            symbol = (
                <CheckCircleIcon
                    className="h-5 w-5 text-green-400"
                    aria-hidden="true"
                />
            );
            break;
    }

    const title = useRef(null);

    const [dismissed, setDismissed] = useState(false);

    const handleDismiss = (e) => {
        if (props.handleDismiss) {
            props.handleDismiss(e);
        }
        setDismissed(true);
    };

    // Reshow if props change - means alert changed
    useEffect(() => {
        setDismissed(false);
    }, [props]);

    const renderActionButtons = () => {
        let colour;
        switch (props.variant) {
            case "error":
                colour =
                    "bg-red-50 text-red-800 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50";
                break;
            case "warning":
                colour =
                    "bg-yellow-50 text-yellow-800 hover:bg-yellow-100 focus:ring-yellow-600 focus:ring-offset-yellow-50";
                break;
            default:
                colour =
                    "bg-green-50 text-green-800 hover:bg-green-100 focus:ring-green-600 focus:ring-offset-green-50";
                break;
        }

        return props.actions.map((item) => {
            return (
                <button
                    key={item.id}
                    onClick={item.onClick}
                    type="button"
                    className={`rounded-md px-2 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 ${colour}`}
                >
                    {item.text}
                </button>
            );
        });
    };

    return (
        <>
            {!dismissed && (
                <div
                    className={`rounded-md p-4 ${variantClass} ${props.className}`}
                >
                    <div className="flex">
                        <div className="flex-shrink-0">{symbol}</div>
                        <div className="ml-3">
                            <h3
                                ref={title}
                                className={`text-sm font-medium ${titleClass}`}
                            >
                                {props.title}
                            </h3>
                            <div className="mt-2 text-sm">{props.children}</div>
                            {props.actions && (
                                <div className="mt-4">
                                    <div className="-mx-2 -my-1.5 flex">
                                        {renderActionButtons()}
                                    </div>
                                </div>
                            )}
                        </div>
                        {(props.dismissable || props.handleDismiss) && (
                            <div className="ml-auto pl-3">
                                <div className="-mx-1.5 -my-1.5">
                                    <button
                                        type="button"
                                        className={`inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 ${dismissColour}`}
                                        onClick={handleDismiss}
                                    >
                                        <span className="sr-only">Dismiss</span>
                                        <XMarkIcon
                                            className="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </>
    );
};

export default Alert;
