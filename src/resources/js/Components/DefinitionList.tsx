import React, { ReactNode } from "react";

type Props = {
    items: {
        key: string | number;
        term: ReactNode;
        definition: ReactNode;
    }[];
    verticalPadding: number;
};

export const DefinitionList: React.FC<Props> = ({
    items,
    verticalPadding = 4,
}) => {
    return (
        <div className="mt-5 border-t border-gray-200">
            <dl className="">
                {items.map((item) => {
                    return (
                        <div
                            key={item.key}
                            className={`py-${verticalPadding} sm:grid sm:grid-cols-3 sm:gap-4`}
                        >
                            <dt className="text-sm font-medium text-gray-500">
                                {item.term}
                            </dt>
                            <dd className="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {item.definition}
                            </dd>
                        </div>
                    );
                })}
            </dl>
        </div>
    );
};
