import React, { useEffect, useRef } from "react";

export default function Input({
  type = "text",
  name,
  value,
  className,
  autoComplete,
  required,
  isFocused,
  handleChange,
}) {
  const input = useRef();

  useEffect(() => {
    if (isFocused) {
      input.current.focus();
    }
  }, []);

  return (
    <div className="flex flex-col items-start">
      <input
        type={type}
        name={name}
        value={value}
        className={
          `rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring-2 focus:ring-indigo-300 ` +
          className
        }
        ref={input}
        autoComplete={autoComplete}
        required={required}
        onChange={(e) => handleChange(e)}
      />
    </div>
  );
}
