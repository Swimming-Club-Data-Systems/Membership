import React from "react";
import { useField, useFormikContext } from "formik";
import { Form } from "react-bootstrap";

const DateInput = ({ label, helpText, mb, disabled, ...props }) => {

  const [field, meta] = useField(props);
  const { isSubmitting } = useFormikContext();
  const marginBotton = mb || "mb-3";

  return (
    <>
      <Form.Group className={marginBotton} controlId={props.id || props.name}>
        <Form.Label>{label}</Form.Label>
        <Form.Control
          type="date"
          isValid={meta.touched && !meta.error}
          isInvalid={meta.touched && meta.error}
          disabled={isSubmitting || disabled}
          {...field}
          {...props}
        />

        {meta.touched && meta.error ? (
          <Form.Control.Feedback type="invalid">
            {meta.error}
          </Form.Control.Feedback>
        ) : null}

        {helpText &&
          <Form.Text className="text-muted">
            {helpText}
          </Form.Text>
        }
      </Form.Group>
    </>
  );
};

export default DateInput;