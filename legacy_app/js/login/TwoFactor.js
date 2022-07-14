
import React, { useState } from "react";
import * as tenantFunctions from "../classes/Tenant";
import { Formik, useFormikContext } from "formik";
import * as yup from "yup";
import { Alert, Form } from "react-bootstrap";
import { Button } from "react-bootstrap";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import axios from "axios";

const schema = yup.object().shape({
  authCode: yup.string().length(6, "Authentication codes are 6 digits").required("You must enter an authentication code").matches(/[0-9]{6,6}/, "Authentication codes are 6 digits"),
  setUpTwoFactor: yup.bool(),
});

const AutoSubmitToken = () => {
  // Grab values and submitForm from context
  const { values, submitForm } = useFormikContext();
  React.useEffect(() => {
    // Submit the form imperatively as an effect as soon as form values.authCode are 6 digits long
    if (values.authCode.length === 6) {
      submitForm();
    }
  }, [values, submitForm]);
  return null;
};

const TwoFactor = (props) => {

  const [enableResend, setEnableResend] = useState(true);
  const [resent, setResent] = useState(false);
  const [error, setError] = useState({
    show: false,
    message: null,
  });

  const handleResend = async () => {
    setEnableResend(false);

    // Make API request
    const response = await axios.post("/api/auth/login/resend-two-factor", {
    });

    if (response.data.success) {
      setResent(true);
      props.setLoginDetail("two_factor_method", "email");
    } else {
      // Show error
      setError({
        show: true,
        message: response.data.message,
      });
    }

    new Promise(function () {
      setTimeout(function () {
        setEnableResend(true);
      }, 5000);
    });
  };

  const onSubmit = async (values, { setSubmitting }) => {
    setSubmitting(true);

    try {
      const response = await axios.post("/api/auth/login/two-factor", {
        auth_code: values.authCode,
        target: props.target,
        setup_two_factor: values.setUpTwoFactor,
        remember_me: props.remember_me || false,
      });

      if (response.data.success) {
        // Redirect for cookies
        setError({
          show: false,
          message: null,
        });
        window.location.replace(response.data.redirect_url);
      } else {
        // Oops
        setSubmitting(false);
        setError({
          show: true,
          message: response.data.message,
        });
      }
    } catch (error) {
      setError({
        show: true,
        message: "A network error occurred",
      });
      setSubmitting(false);
    }
  };

  tenantFunctions.setTitle("Confirm Login");

  let helpString = "We've sent a confirmation code to your registered email address";
  let helpResendString = "Resend email";
  if (props.two_factor_method === "totp") {
    helpString = "Use the code from your Two Factor Authentication app to confirm your sign in";
    helpResendString = "Resend email";
  }

  return (

    <>

      {error.show && (
        <Alert variant="danger">
          <p className="mb-0">
            <strong>Oops</strong>
          </p>
          <p className="mb-0">
            {error.message}
          </p>
        </Alert>
      )}

      <Formik
        validationSchema={schema}
        onSubmit={onSubmit}
        initialValues={{
          authCode: "",
          setUpTwoFactor: false,
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
                <Form.Label>Authentication code</Form.Label>
                <Form.Control
                  name="authCode"
                  autoFocus
                  value={values.authCode}
                  onChange={handleChange}
                  isValid={touched.authCode && !errors.authCode}
                  isInvalid={touched.authCode && errors.authCode}
                  size="lg"
                  placeholder="654321"
                  pattern="[0-9]*"
                  inputMode="numeric"
                  autoComplete="one-time-code"
                />
                {errors.authCode &&
                  <Form.Control.Feedback type="invalid">{errors.authCode}</Form.Control.Feedback>
                }
              </Form.Group>
            </div>

            {!props.has_totp &&
              <Form.Group className="mb-3">
                <Form.Check
                  name="setUpTwoFactor"
                  label="Set up an authenticator app"
                  onChange={handleChange}
                  isInvalid={!!errors.setUpTwoFactor}
                  feedback={errors.setUpTwoFactor}
                  id="setUpTwoFactor"
                  checked={values.setUpTwoFactor}
                />
              </Form.Group>
            }

            <p className="mb-5">
              <Button size="lg" type="submit" disabled={!dirty || !isValid || isSubmitting}>Confirm login</Button>
            </p>

            <div className="mb-5">
              <p>
                {helpString}
              </p>
              <span>
                <Button
                  variant="dark"
                  onClick={handleResend}
                  disabled={!enableResend}
                >
                  {helpResendString}
                </Button>
                {
                  resent &&
                  <Alert variant="info" className="mt-2">
                    We&apos;ve resent your authentication code.
                  </Alert>
                }
              </span>
            </div>

            <AutoSubmitToken />
          </Form>
        )}
      </Formik>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(TwoFactor);