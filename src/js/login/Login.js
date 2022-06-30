
import React, { useState, useEffect, useRef } from "react";
import * as tenantFunctions from "../classes/Tenant";
import { Formik } from "formik";
import * as yup from "yup";
import Button from "../components/Button";
import Alert from "../components/Alert";
import { useLocation, useSearchParams } from "react-router-dom";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import axios from "axios";
// import { useLogin } from "@web-auth/webauthn-helper";
import useLogin from "./useLogin";
import BaseTextInput from "../components/form/base/BaseTextInput";
import Checkbox from "../components/form/base/BaseRadioCheck";
import Link from "../components/Link";

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
  const [userExists, setUserExists] = useState(false);
  const [ssoUrl, setSsoUrl] = useState(null);
  const [selectedTraditional, setSelectedTraditional] = useState(null);
  const [showPasskey, setShowPasskey] = useState(null);
  const [showPassword, setShowPassword] = useState(null);
  const [showSso, setShowSso] = useState(null);

  const schemaItems = {
    emailAddress: yup.string().email("Your email address must be valid").required("You must provide an email address"),
  };

  if (showPassword) {
    schemaItems.password = yup.string().required("You must provide a password");
    schemaItems.rememberMe = yup.bool();
  }

  const schema = yup.object().shape(schemaItems);

  const webAuthnError = {
    type: "danger",
    message: "Passkey authentication failed.",
  };

  const show = (field) => {
    setShowPasskey(field === "passkey");
    setShowPassword(field === "password");
    setShowSso(field === "sso");
  };

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

    const email = value || username;

    // Validate
    const isValidEmail = yup.string().email();
    if (!isValidEmail.validateSync(email)) return;

    // Check for tokens first!
    const { data } = await axios.get("/api/auth/login/has-webauthn", {
      params: {
        email: email,
      }
    });

    setUserExists(data.user_exists);
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
    actionUrl: "/api/auth/login/webauthn-verify",
    optionsUrl: "/api/auth/login/webauthn-challenge",
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

    if (ssoUrl) {
      window.location.href = ssoUrl;
      return;
    } else {
      try {

        const response = await axios.post("/api/auth/login/login", {
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
    }
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

      <div>

        {
          location.state && location.state.successfulReset &&
          <Alert variant="success" title="Success">
            <p>
              <strong>Your password was reset successfully</strong>
            </p>
          </Alert>
        }

        {supportsWebauthn &&
          <div className="mb-6">
            <div>
              <p className="text-sm font-medium text-gray-700">Sign in with</p>

              <div className="mt-1 grid grid-cols-1 gap-3">
                <Button
                  variant="secondary"
                  onClick={handleLogin}
                  fullWidth
                >
                  Passkey
                </Button>
              </div>
            </div>

            <div className="mt-6 relative">
              <div className="absolute inset-0 flex items-center" aria-hidden="true">
                <div className="w-full border-t border-gray-300" />
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="px-2 bg-white text-gray-500">Or continue with</span>
              </div>
            </div>
          </div>
        }

        <div>
          {
            error &&
            <Alert variant="error" title="Error">
              <p>{error.message}</p>
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
                <form noValidate onSubmit={handleSubmit} onBlur={handleBlur}>
                  <BaseTextInput
                    label="Email address"
                    type="text"
                    name="emailAddress"
                    value={values.emailAddress}
                    onChange={async (e) => { handleChange(e); setUsername(e.target.value); await checkWebauthn(e.target.value); }}
                    // isValid={touched.emailAddress && !errors.emailAddress}
                    error={touched.emailAddress && errors.emailAddress}
                    // size="lg"
                    autoComplete="username webauthn"
                  />

                  {(!errors.emailAddress && userExists) &&
                    <>
                      {showPassword &&
                        <BaseTextInput
                          label="Password"
                          type="password"
                          name="password"
                          value={values.password}
                          onChange={handleChange}
                          // isValid={touched.password && !errors.password}
                          error={touched.password && errors.password}
                          // size="lg"
                          autoComplete="current-password"
                        />
                      }

                      {showSso &&
                        <div className="mb-5">
                          <Button fullWidth size="lg" type="submit">Login</Button>
                        </div>
                      }

                      {showPasskey &&
                        <div className="mb-5">

                          <div className="row justify-content-between align-items-center">
                            <div className="col-sm-auto">
                              <Button fullWidth size="lg" type="button" onClick={handleLogin} disabled={false}>Login with passkey</Button>
                              <div className="mb-2 d-sm-none"></div>
                            </div>

                            <div className="col-sm-auto">
                              {renderSwitchModeButton()}
                            </div>
                          </div>

                        </div>
                      }

                      {showPassword &&
                        <>
                          <Checkbox
                            type="checkbox"
                            name="rememberMe"
                            label="Keep me logged in"
                            onChange={handleChange}
                            checked={values.rememberMe}
                            error={touched.rememberMe && errors.rememberMe}
                          />

                          {showTraditional &&
                            <div className="mb-5">
                              <div className="row justify-content-between align-items-center">
                                <div className="col-sm-auto">
                                  <Button fullWidth size="lg" type="submit" disabled={!dirty || !isValid || isSubmitting}>Login</Button>
                                  <div className="mb-2 d-sm-none"></div>
                                </div>

                                <div className="col-sm-auto">
                                  {renderSwitchModeButton()}
                                </div>
                              </div>
                            </div>
                          }
                        </>
                      }
                    </>
                  }

                  <p className="mb-4">
                    New member? Your club will create an account for you and send you a link to get started.
                  </p>
                  <p>
                    <Link to="/login/forgot-password">
                      Reset password
                    </Link>
                  </p>
                </form>
              );
            }
            }
          </Formik>
        </div >
      </div >

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(Login);