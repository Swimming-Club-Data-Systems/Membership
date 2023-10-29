import React from "react";
import { formatDate } from "@/Utils/date-utils";

type ItemProps = {
    start: string;
    end: string;
    closing_balance: number;
    closing_balance_formatted: string;
};

export const StatementIndexItemContent: React.FC<ItemProps> = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {formatDate(props.start)} - {formatDate(props.end)}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            Closing balance: {props.closing_balance_formatted}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};
