import React from "react";
import { useField, useFormikContext } from "formik";
import { Form } from "react-bootstrap";

const Select = ({ label, helpText, mb, disabled, ...props }) => {

  const [field, meta] = useField(props);
  const { isSubmitting } = useFormikContext();
  const marginBotton = mb || "mb-3";

  const options = [{value: "select", name: "Select an option", disabled: true}, ...props.options];

  return (
    <>
      <Form.Group className={marginBotton} controlId={props.id || props.name}>
        <Form.Label>{label}</Form.Label>
        <Form.Select
          isValid={meta.touched && !meta.error}
          isInvalid={meta.touched && meta.error}
          disabled={isSubmitting || disabled}
          {...field}
          {...props}
        >
          {options.map((item) => <option disabled={item.disabled} value={item.value} key={item.value}>{item.name}</option>)}
        </Form.Select>

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

export default Select;