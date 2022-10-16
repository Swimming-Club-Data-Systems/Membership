import React from "react";

const BasicList = ({items}) => {
    return (
        <ul role="list" className="divide-y divide-gray-200">
            {items.map((item) => (
                <li key={item.id} className="py-4">
                    {item.content}
                </li>
            ))}
        </ul>
    );
};

export default BasicList;