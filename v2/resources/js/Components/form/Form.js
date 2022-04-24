/**
 * Form component
 */

import React from "react";
import { Formik, Form as FormikForm, useFormikContext } from "formik";
import { usePage } from "@inertiajs/inertia-react"
import Button from "../Button";

const SubmissionButtons = (props) => {

  const { isSubmitting, dirty, isValid, errors, handleReset } = useFormikContext();

  const clearForm = () => {
    if (props.onClear) {
      props.onClear();
    }
    handleReset();
  };

  return (
    <>
      {
        false && errors &&
        <p className="text-end text-danger">
          There are <strong>{Object.keys(errors).length} errors</strong>
        </p>
      }
      <div className="text-right">
        {!props.hideClear &&
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
        }

        <Button
          type="submit"
          disabled={!dirty || !isValid || isSubmitting}
        >
          {props.submitTitle || "Submit"}
        </Button>
      </div>
    </>
  );
};

const Form = (props) => {

  const { serverSideErrors } = usePage().props;
  console.log(serverSideErrors);

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

  return (
    <>
      <Formik
        initialValues={initialValues}
        validationSchema={validationSchema}
        onSubmit={onSubmit}
      >
        <FormikForm {...otherProps}>
          {props.children}

          {!hideDefaultButtons &&
            <SubmissionButtons
              submitTitle={submitTitle}
              hideClear={hideClear}
              clearTitle={clearTitle}
              onClear={onClear}
            />
          }
        </FormikForm>
      </Formik>
    </>
  );
};

export default Form;