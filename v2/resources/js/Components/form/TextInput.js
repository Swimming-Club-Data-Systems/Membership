import React from "react";
import { useField, useFormikContext } from "formik";

const TextInput = ({ label, help, mb, disabled, type, leftText, rightText, className = '', ...props }) => {

  const [field, meta] = useField(props);
  const { isSubmitting } = useFormikContext();
  const marginBotton = mb || "mb-3";
  const isValid = props.showValid && meta.touched && !meta.error;
  const isInvalid = meta.touched && meta.error;
  const controlId = props.id || props.name;

  if (!type) {
    type = "text";
  }

  return (
    <>
      <div className={marginBotton}>
        <label htmlFor={controlId} className="block text-sm font-medium text-gray-700">{label}</label>
        <input
          disabled={isSubmitting || disabled}
          className={"mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md " + className}
          id={controlId}
          type={type}
          {...field}
          {...props}
        />

        {meta.touched && meta.error ? (
          <p>
            {meta.error}
          </p>
        ) : null}

        {help &&
          <p className="mt-2 text-sm text-gray-500">
            {help}
          </p>
        }
      </div>
    </>
  );
};

export default TextInput;