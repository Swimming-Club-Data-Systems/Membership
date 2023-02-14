import React from "react";

type Props = {
    name: string;
    stat: string | number;
};

const Stat: React.FC<Props> = (props) => {
    return (
        <div className="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt className="truncate text-sm font-medium text-gray-500">
                {props.name}
            </dt>
            <dd className="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                {props.stat}
            </dd>
        </div>
    );
};

export default Stat;
