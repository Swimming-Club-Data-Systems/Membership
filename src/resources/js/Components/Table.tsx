import React from "react";
import { get } from "lodash";

type TableProps = {
    columns: {
        key?: string | number;
        id?: string | number;
        headerName: string;
        field: string;
        default?: string | number;
        render?: (
            value: string | number | React.ReactNode,
        ) => string | number | React.ReactNode;
    }[];
    data: object[];
};

const Table: React.FC<TableProps> = (props) => {
    return (
        <div className="flex flex-col">
            <div className="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div className="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <table className="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                {props.columns.map((item, idx) => {
                                    const key = item.key || item.id;
                                    return (
                                        <th
                                            key={`column-title-${key}`}
                                            scope="col"
                                            className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 md:pl-0"
                                        >
                                            {item.headerName}
                                        </th>
                                    );
                                })}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200">
                            {props.data.map((row, idx) => {
                                const key = get(row, "id", idx);
                                return (
                                    <tr key={`row-${key}`}>
                                        {props.columns.map((column) => {
                                            const value = get(
                                                row,
                                                column.field,
                                                column.default || null,
                                            );

                                            return (
                                                <td
                                                    key={column.field}
                                                    className="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 md:pl-0"
                                                >
                                                    {column.render
                                                        ? column.render(value)
                                                        : value}
                                                </td>
                                            );
                                        })}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default Table;
