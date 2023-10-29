import React from "react";
import { useSelector } from "react-redux";
import { apiCount } from "@/Reducers/store";

export interface Props extends React.ComponentProps<"button"> {
    className?: string;
    variant?: "primary" | "secondary" | "danger" | "warning" | "success";
    disabled?: boolean;
}

const Button: React.FC<Props> = ({
    type = "button",
    className = "",
    children,
    variant = "primary",
    ...props
}) => {
    let variantStyle;
    switch (variant) {
        case "secondary":
            variantStyle =
                "bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-400";
            break;
        case "danger":
            variantStyle =
                "border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 disabled:bg-red-400";
            break;
        case "warning":
            variantStyle =
                "border-transparent text-black bg-amber-400 hover:bg-amber-500 focus:ring-amber-300 disabled:bg-amber-200";
            break;
        case "success":
            variantStyle =
                "border-transparent text-black bg-green-400 hover:bg-green-500 focus:ring-green-300 disabled:bg-green-200";
            break;
        case "primary":
        default:
            variantStyle =
                "border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 disabled:bg-indigo-400";
            break;
    }

    const count = useSelector(apiCount);

    return (
        <button
            type={type}
            className={
                `inline-flex justify-center rounded-md border py-2 px-4 text-sm font-sans font-medium shadow-sm ${variantStyle} focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none ` +
                className
            }
            disabled={props.disabled || count > 0}
            {...props}
        >
            {children}
        </button>
    );
};

export default Button;
