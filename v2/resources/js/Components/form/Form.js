/**
 * Form component
 */

import React from "react";
import { Formik, Form as FormikForm, useFormikContext } from "formik";

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
      <div className="row">
        <div className="col-auto ms-auto">
          {!props.hideClear &&
            <>

              <button
                type="button"
                className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                onClick={clearForm}
                disabled={isSubmitting || !dirty}
              >
                {props.clearTitle || "Clear"}
              </button>{" "}
            </>
          }

          <button
            type="submit"
            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            disabled={!dirty || !isValid || isSubmitting}
          >
            {props.submitTitle || "Submit"}
          </button>
        </div>
      </div>
    </>
  );
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

          <SubmissionButtons
            submitTitle={submitTitle}
            hideClear={hideClear}
            clearTitle={clearTitle}
            onClear={onClear}
          />
        </FormikForm>
      </Formik>
    </>
  );
};

export default Form;