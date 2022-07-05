
import React, { useEffect, useState } from "react";
import * as tenantFunctions from "../classes/Tenant";
import { Formik } from "formik";
import * as yup from "yup";
import Alert from "../components/Alert";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import axios from "axios";
import BaseTextInput from "../components/form/base/BaseTextInput";
import Button from "../components/Button";

const schema = yup.object().shape({
  emailAddress: yup.string().email("Your email address must be valid").required("You must provide an email address"),
});

const FindAccount = (props) => {

  useEffect(() => {
    tenantFunctions.setTitle("Get back into your account");
    props.setType("resetPassword");
  }, []);

  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const onSubmit = async (values, { setSubmitting }) => {
    setSubmitting(true);

    try {

      const response = await axios.post("/api/auth/request-password-reset", {
        email_address: values.emailAddress,
      });

      if (response.data.success) {
        setSuccess(true);
      } else {
        // There was an error
        setError({
          type: "danger",
          message: response.data.message,
        });
      }

    } catch (error) {
      setError({
        type: "danger",
        message: error.message,
      });
    }

    setSubmitting(false);
  };

  return (

    <>
      {success &&
        <>
          <Alert variant="success" title="We've found your account" className="mb-4">
            <p>
              We&apos;re sending you an email with instructions detailing how to reset your password.
            </p>
          </Alert>
        </>
      }

      {!success &&
        <>
          {
            error &&
            <Alert variant="error" title="Error" className="mb-4">{error.message}</Alert>
          }

          <Formik
            validationSchema={schema}
            onSubmit={onSubmit}
            initialValues={{
              emailAddress: props.emailAddress || "",
              password: "",
              rememberMe: props.rememberMe || true,
            }}
          >
            {({
              handleSubmit,
              handleChange,
              handleBlur,
              values,
              touched,
              isValid,
              errors,
              isSubmitting,
              dirty,
            }) => (
              <form noValidate onSubmit={handleSubmit} onBlur={handleBlur}>
                <BaseTextInput
                  label="Email address"
                  type="email"
                  name="emailAddress"
                  value={values.emailAddress}
                  onChange={handleChange}
                  error={touched.emailAddress && errors.emailAddress}
                />

                <p>
                  <Button type="submit" disabled={!dirty || !isValid || isSubmitting}>Reset password</Button>
                </p>
              </form>
            )}
          </Formik>
        </>
      }
    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(FindAccount);