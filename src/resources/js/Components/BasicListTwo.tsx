import React, { ReactNode } from "react";

type BasicListTwoProps = {
    children: ReactNode;
};

export const BasicListTwo = ({ children }: BasicListTwoProps) => {
    return (
        <ul role="list" className="divide-y divide-gray-200">
            {children}
        </ul>
    );
};

type BasicListTwoItemProps = {
    key: string | number;
    children: ReactNode;
};

export const BasicListTwoItem = (props: BasicListTwoItemProps) => {
    return <li className="py-4">{props.children}</li>;
};
