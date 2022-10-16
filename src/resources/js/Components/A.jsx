import React from "react";

const A = ({ className, ...props }) => {
    return (
        <a
            className={`text-indigo-600 hover:text-indigo-700 hover:underline ${
                className || ""
            }`}
            {...props}
        />
    );
};

export default A;
