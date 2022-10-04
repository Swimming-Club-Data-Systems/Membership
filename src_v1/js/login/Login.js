
import React, { useState, useEffect, useRef } from "react";
import * as tenantFunctions from "../classes/Tenant";
import { Formik } from "formik";
import * as yup from "yup";
import { Form } from "react-bootstrap";
import { Button } from "react-bootstrap";
import { Alert } from "react-bootstrap";
import { Link, useLocation, useSearchParams } from "react-router-dom";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import axios from "axios";
// import { useLogin } from "@web-auth/webauthn-helper";
import useLogin from "./useLogin";
import { Collapse } from "bootstrap";

const schema = yup.object().shape({
  emailAddress: yup.string().email("Your email address must be valid").required("You must provide an email address"),
  password: yup.string().required("You must provide a password"),
  rememberMe: yup.bool(),
});

const Login = (props) => {

  const supportsWebauthn = typeof (PublicKeyCredential) !== "undefined";

  const location = useLocation();
  const [searchParams] = useSearchParams();

  useEffect(() => {
    tenantFunctions.setTitle("Login");
    if (location.state && location.state.location) {
      props.setLoginDetails({
        target: location.state.location.pathname,
      });
    }
  }, []);

  const [error, setError] = useState(null);
  const [username, setUsername] = useState("");
  const [hasWebauthn, setHasWebauthn] = useState(false);
  const [ssoUrl, setSsoUrl] = useState(null);
  const [selectedTraditional, setSelectedTraditional] = useState(null);
  const collapsePasskeyRef = useRef();
  const [collapsePasskey, setCollapsePasskey] = useState(null);
  const collapsePasswordRef = useRef();
  const [collapsePassword, setCollapsePassword] = useState(null);
  const collapseSsoRef = useRef();
  const [collapseSso, setCollapseSso] = useState(null);

  const webAuthnError = {
    type: "danger",
    message: "Passkey authentication failed.",
  };

  const show = (field) => {
    if (field === "passkey") {
      collapsePasskey.show();
    } else {
      collapsePasskey.hide();
    }

    if (field === "sso") {
      collapseSso.show();
    } else {
      collapseSso.hide();
    }

    if (field === "password") {
      collapsePassword.show();
    } else {
      collapsePassword.hide();
    }
  };

  useEffect(() => {
    setCollapsePasskey(new Collapse(collapsePasskeyRef.current, { toggle: false }));
    setCollapsePassword(new Collapse(collapsePasswordRef.current, { toggle: false }));
    setCollapseSso(new Collapse(collapseSsoRef.current, { toggle: false }));
  }, []);

  useEffect(() => {
    if (selectedTraditional !== null) {
      show(selectedTraditional ? "password" : "passkey");
    }
  }, [selectedTraditional]);

  const checkWebauthn = async (value = null) => {
    if (!supportsWebauthn) {
      // Not supported in browser so do not show
      return false;
    }

    // Check for tokens first!
    const { data } = await axios.get("/v1/api/auth/login/has-webauthn", {
      params: {
        email: value || username,
      }
    });

    setHasWebauthn(data.has_webauthn);
    if (data.is_sso) {
      setSsoUrl(data.sso_url);
      show("sso");
    } else {
      setSsoUrl(null);
      show(data.has_webauthn ? "passkey" : "password");
    }

    return data.has_webauthn;
  };

  const login = useLogin({
    actionUrl: "/v1/api/auth/login/webauthn-verify",
    optionsUrl: "/v1/api/auth/login/webauthn-challenge",
  });

  const handleLogin = async (event, autoFill = false) => {
    try {
      const requestObject = {
        target: location?.state?.location?.pathname,
      };

      if (username) {
        requestObject.username = username;
        const hasTokens = await checkWebauthn();
        if (!hasTokens) {
          setError({ variant: "warning", message: "There are no passkeys registered for this account." });
          return;
        }
      }

      if (autoFill) {
        requestObject.credentialsGetProps = {
          mediation: "conditional"
        };
      }

      const response = await login(requestObject);
      if (response.success) {
        window.location.replace(response.redirect_url);
        setError(null);
      } else {
        setError(webAuthnError);
        console.error(error);
      }
    } catch (error) {
      setError(webAuthnError);
      console.error(error);
    }
  };

  // eslint-disable-next-line no-unused-vars
  const handleAutofillLogin = async () => {
    // eslint-disable-next-line no-undef
    if (!PublicKeyCredential.isConditionalMediationAvailable ||
      // eslint-disable-next-line no-undef
      !PublicKeyCredential.isConditionalMediationAvailable()) {
      // Browser doesn't support AutoFill-assisted requests.
      return;
    }

    await handleLogin(null, true);
  };

  useEffect(() => {
    (async () => {
      await handleAutofillLogin();
    })();
  }, []);

  const onSubmit = async (values, { setSubmitting }) => {
    setSubmitting(true);

    try {

      const response = await axios.post("/v1/api/auth/login/login", {
        email_address: values.emailAddress,
        password: values.password,
      });

      if (response.data.success) {
        props.setType("twoFactor");
        props.setLoginDetails({
          ...response.data,
          remember_me: values.rememberMe
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

  const renderSwitchModeButton = () => {
    return (
      <Button variant="secondary" type="button" onClick={() => setSelectedTraditional(!selectedTraditional)} disabled={false}>
        {selectedTraditional ? "Use a passkey to login" : "Use a password to login"}
      </Button>
    );
  };

  return (

    <>

      {
        error &&
        <div className="alert alert-danger">{error.message}</div>
      }

      {
        location.state && location.state.successfulReset &&
        <Alert variant="success">
          <p className="mb-0">
            <strong>Your password was reset successfully</strong>
          </p>
        </Alert>
      }

      <Formik
        validationSchema={schema}
        onSubmit={onSubmit}
        initialValues={{
          emailAddress: props.emailAddress || searchParams.get("email") || "",
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
        }) => {
          const showTraditional = (!hasWebauthn && touched.emailAddress && !errors.emailAddress) || selectedTraditional;
          return (
            <Form noValidate onSubmit={handleSubmit} onBlur={handleBlur}>
              <div className="mb-3">
                <Form.Group controlId="emailAddress">
                  <Form.Label>Email address</Form.Label>
                  <Form.Control
                    type="text"
                    name="emailAddress"
                    value={values.emailAddress}
                    onChange={async (e) => { handleChange(e); setUsername(e.target.value); await checkWebauthn(e.target.value); }}
                    isValid={touched.emailAddress && !errors.emailAddress}
                    isInvalid={touched.emailAddress && errors.emailAddress}
                    size="lg"
                    autoComplete="username webauthn"
                  />
                  {errors.emailAddress &&
                    <Form.Control.Feedback type="invalid">{errors.emailAddress}</Form.Control.Feedback>
                  }
                </Form.Group>
              </div>

              <div className="collapse" ref={collapseSsoRef}>
                <div className="mb-5">
                  <Button href={ssoUrl} size="lg" type="button">Login</Button>
                </div>
              </div>

              <div className="collapse" ref={collapsePasskeyRef}>
                {
                  // (supportsWebauthn && !showTraditional && hasWebauthn) &&
                  <div className="mb-5">

                    <div className="row justify-content-between align-items-center">
                      <div className="col-sm-auto">
                        <Button size="lg" type="button" onClick={handleLogin} disabled={false}>Login with passkey</Button>
                        <div className="mb-2 d-sm-none"></div>
                      </div>

                      <div className="col-sm-auto">
                        {renderSwitchModeButton()}
                      </div>
                    </div>

                  </div>
                }
              </div>

              <div className="collapse" ref={collapsePasswordRef}>
                <div className="mb-3">
                  <Form.Group controlId="password">
                    <Form.Label>Password</Form.Label>
                    <Form.Control
                      type="password"
                      name="password"
                      value={values.password}
                      onChange={handleChange}
                      isValid={touched.password && !errors.password}
                      isInvalid={touched.password && errors.password}
                      size="lg"
                      autoComplete="current-password"
                    />
                    {errors.password &&
                      <Form.Control.Feedback type="invalid">{errors.password}</Form.Control.Feedback>
                    }
                  </Form.Group>
                </div>

                <Form.Group className="mb-3">
                  <Form.Check
                    name="rememberMe"
                    label="Keep me logged in"
                    onChange={handleChange}
                    checked={values.rememberMe}
                    isInvalid={!!errors.rememberMe}
                    feedback={errors.rememberMe}
                    feedbackType="invalid"
                    id="rememberMe"
                  />
                </Form.Group>

                {
                  showTraditional &&
                  <div className="mb-5">
                    <div className="row justify-content-between align-items-center">
                      <div className="col-sm-auto">
                        <Button size="lg" type="submit" disabled={!dirty || !isValid || isSubmitting}>Login</Button>
                        <div className="mb-2 d-sm-none"></div>
                      </div>

                      <div className="col-sm-auto">
                        {renderSwitchModeButton()}
                      </div>
                    </div>
                  </div>
                }
              </div>

              <div className="mb-5">
                <p>
                  New member? Your club will create an account for you and send you a link to get started.
                </p>
                <span>
                  <Link to="/login/forgot-password" className="btn btn-dark">
                    Forgot password?
                  </Link>
                </span>
              </div>
            </Form>
          );
        }
        }
      </Formik>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(Login);