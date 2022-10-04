
import React, { useEffect, useState } from "react";
import * as tenantFunctions from "../classes/Tenant";
import { Formik } from "formik";
import * as yup from "yup";
import { Alert, Form } from "react-bootstrap";
import { Button } from "react-bootstrap";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import axios from "axios";
import { isPwned } from "../classes/Passwords";
import { useNavigate, useSearchParams } from "react-router-dom";
import Loader from "../components/Loader";

const schema = yup.object().shape({
  password: yup.string().required("You must provide a password").min(8, "Your password must be at least 8 characters").matches(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/, "Your password must contain at least one lower case letter, one upper case letter and one number").test(
    "is-pwned",
    "Your password is insecure",
    async (value) => await isPwned(value),
  ),
  confirmPassword: yup.string().required("You must confirm your password").oneOf([yup.ref("password"), null], "Passwords do not match"),
});

const ResetPassword = (props) => {

  const [error, setError] = useState(null);
  const [isValid, setIsValid] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  useEffect(() => {
    const getData = async () => {
      tenantFunctions.setTitle("Get back into your account");
      props.setType("resetPassword");

      // Check token
      const response = await axios.post("/api/auth/can-password-reset", {
        token: searchParams.get("auth-code"),
      });
      setIsValid(response.data.success);

      setLoaded(true);

    };

    getData();

  }, []);

  const onSubmit = async (values, { setSubmitting }) => {
    setSubmitting(true);

    try {

      const response = await axios.post("/api/auth/complete-password-reset", {
        token: searchParams.get("auth-code"),
        password: values.password,
      });

      if (response.data.success) {
        // Redirect to login with state
        navigate("/login", {
          state: {
            successfulReset: true,
          }
        });
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

    <Loader loaded={loaded}>

      {!isValid &&
        <>
          <Alert variant="danger">
            <p className="mb-0"><strong>We couldn&apos;t find a matching password reset request</strong></p>
            <p className="mb-0">Please try checking the link in the password reset email we sent you. Reset links expire after two days.</p>
          </Alert>
        </>
      }

      {isValid &&
        <>
          {
            error &&
            <div className="alert alert-danger">{error.message}</div>
          }

          <Formik
            validationSchema={schema}
            onSubmit={onSubmit}
            initialValues={{
              password: "",
              confirmPassword: "",
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
              <Form noValidate onSubmit={handleSubmit} onBlur={handleBlur}>
                <div className="mb-3">
                  <Form.Group controlId="password">
                    <Form.Label>New password</Form.Label>
                    <Form.Control
                      type="password"
                      name="password"
                      value={values.password}
                      onChange={handleChange}
                      isValid={touched.password && !errors.password}
                      isInvalid={touched.password && errors.password}
                      size="lg"
                      autoComplete="new-password"
                    />
                    {errors.password &&
                      <Form.Control.Feedback type="invalid">{errors.password}</Form.Control.Feedback>
                    }
                  </Form.Group>
                </div>

                <div className="mb-3">
                  <Form.Group controlId="confirmPassword">
                    <Form.Label>Confirm password</Form.Label>
                    <Form.Control
                      type="password"
                      name="confirmPassword"
                      value={values.confirmPassword}
                      onChange={handleChange}
                      isValid={touched.confirmPassword && !errors.confirmPassword}
                      isInvalid={touched.confirmPassword && errors.confirmPassword}
                      size="lg"
                      autoComplete="new-password"
                    />
                    {errors.confirmPassword &&
                      <Form.Control.Feedback type="invalid">{errors.confirmPassword}</Form.Control.Feedback>
                    }
                  </Form.Group>
                </div>

                <p className="mb-5">
                  <Button size="lg" type="submit" disabled={!dirty || !isValid || isSubmitting}>Change password</Button>
                </p>
              </Form>
            )}
          </Formik>
        </>
      }

    </Loader>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(ResetPassword);