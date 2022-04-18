import React, { useState } from "react";
import { useField, useFormikContext } from "formik";
import { v4 } from "uuid";

const RadioCheck = ({ type, label, mb, disabled, ...props }) => {

  const marginBotton = mb || "mb-3";

  const [field, meta] = useField({
    type,
    ...props
  });
  const { isSubmitting } = useFormikContext();

  let feedback = null;
  let feedbackType = null;
  if (meta.touched && meta.error) {
    feedback = meta.error;
    feedbackType = "invalid";
  }

  const [id] = useState(v4());

  const isValid = props.showValid && meta.touched && !meta.error;
  const isInvalid = meta.touched && meta.error;

  let checkStyles = "";
  if (type === "checkbox") {
    checkStyles = "rounded";
  }

  return (
    <div className={`flex items-start ${marginBotton}`}>
      <div className="flex items-center h-5">
        <input
          id={id}
          {...field}
          {...props}
          disabled={isSubmitting || disabled}
          type={type}
          className={`focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 ${checkStyles}`}
        />
      </div>
      <div className="ml-3 text-sm">
        <label htmlFor={id} className="font-medium text-gray-700">
          {label}
        </label>
        {props.help &&
          <p className="text-gray-500">{props.help}</p>
        }
      </div>
    </div>
  );
};

export default RadioCheck;