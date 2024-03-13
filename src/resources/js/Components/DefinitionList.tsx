import React, { ReactNode } from "react";

export type DefinitionListItemProps = {
    /** A unique ID for the item */
    key: string | number;
    /** The term/title of the item */
    term: ReactNode;
    /** The definition of the item */
    definition: ReactNode;
    /** Whether to truncate the string to prevent text overflow */
    truncate?: boolean;
    /** Whether the item should be rendered as HTML. This is unsafe. */
    unsafe?: boolean;
};

export type DefinitionListProps = {
    items: DefinitionListItemProps[];
    verticalPadding?: number;
};

export const DefinitionList: React.FC<DefinitionListProps> = ({
    items,
    verticalPadding = 2,
}) => {
    return (
        <div className="">
            <dl className="@container">
                {items.map((item) => {
                    return (
                        <div
                            key={item.key}
                            className={`py-${verticalPadding} @sm:grid @sm:grid-cols-3 @sm:gap-4`}
                        >
                            <dt className="text-sm font-medium text-gray-500 @sm:col-start-1 @sm:col-span-1 @sm:row-start-1">
                                {item.term}
                            </dt>
                            <dd
                                className={`mt-1 text-sm text-gray-900 @sm:col-start-2 @sm:col-span-2 @sm:mt-0 @sm:row-start-1 ${
                                    item.truncate ? "truncate" : ""
                                } ${item.unsafe ? "prose prose-sm" : ""}`}
                                dangerouslySetInnerHTML={
                                    item.unsafe
                                        ? { __html: item.definition }
                                        : undefined
                                }
                                children={
                                    item.unsafe ? undefined : item.definition
                                }
                            />
                        </div>
                    );
                })}
            </dl>
        </div>
    );
};
