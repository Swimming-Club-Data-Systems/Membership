import React from "react";
import { useField, useFormikContext } from "formik";
import { ExclamationCircleIcon } from "@heroicons/react/solid";

const TextInput = ({
  label,
  help,
  mb,
  disabled,
  type,
  leftText,
  rightText,
  className = "",
  ...props
}) => {
  const [field, meta] = useField(props);
  const { isSubmitting } = useFormikContext();
  const marginBotton = mb || "mb-3";
  const isValid = props.showValid && meta.touched && !meta.error;
  const isInvalid = meta.touched && meta.error;
  const controlId = props.id || props.name;

  if (!type) {
    type = "text";
  }

  let errorClasses = "";
  if (isInvalid) {
    errorClasses =
      "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
  }

  return (
    <>
      <div className={marginBotton}>
        <label
          htmlFor={controlId}
          className="block text-sm font-medium text-gray-700"
        >
          {label}
        </label>

        <div className="relative mt-1 rounded-md shadow-sm">
          <input
            disabled={isSubmitting || disabled}
            className={`mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
            id={controlId}
            type={type}
            {...field}
            {...props}
          />
          {isInvalid && (
            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
              <ExclamationCircleIcon
                className="h-5 w-5 text-red-500"
                aria-hidden="true"
              />
            </div>
          )}
        </div>

        {help && <p className="mt-2 text-sm text-gray-500">{help}</p>}

        {isInvalid && <p className="mt-2 text-sm text-red-600">{meta.error}</p>}
      </div>
    </>
  );
};

export default TextInput;
