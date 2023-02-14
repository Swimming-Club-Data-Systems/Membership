import React from "react";

type Props = {
    title: string;
    children: React.ReactNode;
};

export const Stats: React.FC<Props> = (props) => {
    return (
        <div>
            <h3 className="text-lg font-medium leading-6 text-gray-900">
                {props.title}
            </h3>
            <dl className="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                {props.children}
            </dl>
        </div>
    );
};

export default Stats;
