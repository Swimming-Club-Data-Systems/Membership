import React from "react";
import {
  ExclamationIcon,
  XCircleIcon,
  CheckCircleIcon,
} from "@heroicons/react/solid";

export const AlertList = ({ children }) => {
  return (
    <ul role="list" className="list-disc space-y-1 pl-5">
      {children}
    </ul>
  );
};

const Alert = (props) => {
  let variantClass, symbol, titleClass;
  switch (props.variant) {
    case "error":
      variantClass = "bg-red-50 text-red-700";
      titleClass = "text-red-800";
      symbol = (
        <XCircleIcon className="h-5 w-5 text-red-400" aria-hidden="true" />
      );
      break;
    case "warning":
      variantClass = "bg-yellow-50 text-yellow-700";
      titleClass = "text-yellow-800";
      symbol = (
        <ExclamationIcon
          className="h-5 w-5 text-yellow-400"
          aria-hidden="true"
        />
      );
      break;
    default:
      variantClass = "bg-green-50 text-green-700";
      titleClass = "text-green-800";
      symbol = (
        <CheckCircleIcon
          className="h-5 w-5 text-green-400"
          aria-hidden="true"
        />
      );
      break;
  }

  const renderActionButtons = () => {
    let colour;
    switch (props.variant) {
      case "error":
        colour =
          "bg-red-50 text-red-800 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50";
        break;
      case "warning":
        colour =
          "bg-yellow-50 text-yellow-800 hover:bg-yellow-100 focus:ring-yellow-600 focus:ring-offset-yellow-50";
        break;
      default:
        colour =
          "bg-green-50 text-green-800 hover:bg-green-100 focus:ring-green-600 focus:ring-offset-green-50";
        break;
    }

    return props.actions.map((item) => {
      return (
        <button
          key={item.id}
          onClick={item.onClick}
          type="button"
          className={`rounded-md px-2 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 ${colour}`}
        >
          {item.text}
        </button>
      );
    });
  };

  return (
    <div className={`rounded-md p-4 ${variantClass} ${props.className}`}>
      <div className="flex">
        <div className="flex-shrink-0">{symbol}</div>
        <div className="ml-3">
          <h3 className={`text-sm font-medium ${titleClass}`}>{props.title}</h3>
          <div className="mt-2 text-sm">{props.children}</div>
          {props.actions && (
            <div className="mt-4">
              <div className="-mx-2 -my-1.5 flex">{renderActionButtons()}</div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Alert;
