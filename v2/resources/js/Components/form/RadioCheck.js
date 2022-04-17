import React from "react";
import { useField, useFormikContext } from "formik";
import { Form } from "react-bootstrap";
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

  return (
    <div className={marginBotton}>
      <Form.Check
        type={type}
        isValid={props.showValid && meta.touched && !meta.error}
        isInvalid={meta.touched && meta.error}
        label={label}
        disabled={isSubmitting || disabled}
        feedback={feedback}
        feedbackType={feedbackType}
        id={v4()}
        {...field}
        {...props}
      />
    </div>
  );
};

export default RadioCheck;