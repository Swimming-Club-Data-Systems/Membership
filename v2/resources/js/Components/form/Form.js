/**
 * Form component
 */

import React, { useEffect } from "react";
import {
  Formik,
  Form as FormikForm,
  useFormikContext,
  ErrorMessage,
} from "formik";
import { usePage } from "@inertiajs/inertia-react";
import Button from "../Button";
import Alert, { AlertList } from "../Alert";

const SubmissionButtons = (props) => {
  const { isSubmitting, dirty, isValid, errors, handleReset } =
    useFormikContext();

  const clearForm = () => {
    if (props.onClear) {
      props.onClear();
    }
    handleReset();
  };

  return (
    <>
      {false && errors && (
        <p className="text-end text-danger">
          There are <strong>{Object.keys(errors).length} errors</strong>
        </p>
      )}
      <div className="text-right">
        {!props.hideClear && (
          <>
            <Button
              type="button"
              onClick={clearForm}
              disabled={isSubmitting || !dirty}
              variant="secondary"
            >
              {props.clearTitle || "Clear"}
            </Button>{" "}
          </>
        )}

        <Button type="submit" disabled={!dirty || !isValid || isSubmitting}>
          {props.submitTitle || "Submit"}
        </Button>
      </div>
    </>
  );
};

const HandleServerErrors = () => {
  const { errors } = usePage().props;
  const { setStatus, setSubmitting } = useFormikContext();

  useEffect(() => {
    if (errors) {
      setStatus(errors);
      setSubmitting(false);
    }
  }, [errors]);

  return null;
};

const RenderServerErrors = () => {
  const errors = useFormikContext().status;

  if (errors) {
    const errorList = [];
    Object.entries(errors).forEach(([key, value]) => {
      errorList.push(<li key={key}>{value}</li>);
    });

    if (errorList.length > 0) {
      return (
        <Alert
          className="mb-3"
          variant="error"
          title="There are errors to correct"
        >
          <AlertList>{errorList}</AlertList>
        </Alert>
      );
    }
  }

  return null;
};

const Form = (props) => {
  const {
    initialValues,
    validationSchema,
    onSubmit,
    submitTitle,
    hideClear,
    clearTitle,
    onClear,
    hideDefaultButtons = false,
    ...otherProps
  } = props;

  console.log(usePage().props);

  const onSubmitHandler = (values, formikBag) => {
    formikBag.setStatus({});
    if (onSubmit) {
      onSubmit(values, formikBag);
    }
  };

  return (
    <>
      <Formik
        initialValues={initialValues}
        validationSchema={validationSchema}
        onSubmit={onSubmitHandler}
      >
        <FormikForm {...otherProps}>
          <HandleServerErrors />

          <RenderServerErrors />

          {props.children}

          {!hideDefaultButtons && (
            <SubmissionButtons
              submitTitle={submitTitle}
              hideClear={hideClear}
              clearTitle={clearTitle}
              onClear={onClear}
            />
          )}
        </FormikForm>
      </Formik>
    </>
  );
};

export default Form;
