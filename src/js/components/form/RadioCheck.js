import React, { useState } from "react";
import { useField, useFormikContext } from "formik";
import { v4 } from "uuid";
import BaseRadioCheck from "./base/BaseRadioCheck";

const RadioCheck = ({ type, disabled, ...props }) => {

  const [field, meta] = useField({
    type,
    ...props,
  });
  const { isSubmitting } = useFormikContext();

  return (
    <BaseRadioCheck
      {...field}
      {...props}
      disabled={isSubmitting || disabled}
      type={type}
      error={meta.touched && meta.error}
    />
  );
};

export default RadioCheck;
