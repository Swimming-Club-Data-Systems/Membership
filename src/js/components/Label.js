import React from "react";

export default function Label({ forInput, value, className, children }) {
  return (
    <label
      htmlFor={forInput}
      className={`block text-sm font-medium text-gray-700 ` + className}
    >
      {value ? value : children}
    </label>
  );
}
