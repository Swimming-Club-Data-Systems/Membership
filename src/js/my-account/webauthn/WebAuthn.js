// import { Formik, Form as FormikForm } from "formik";
import React, { useEffect, useState } from "react";
import * as tenantFunctions from "../../classes/Tenant";
import Header from "../../components/Header";
import Breadcrumb from "../../components/Breadcrumb";
import { Alert, Button, Card, ListGroup, ListGroupItem } from "react-bootstrap";
import axios from "axios";
import { useRegistration } from "@web-auth/webauthn-helper";
import Form from "../../components/form/Form";
import TextInput from "../../components/form/TextInput";
import * as yup from "yup";

const WebAuthn = () => {

  const [error, setError] = useState(null);
  const [username, setUsername] = useState(null);
  const [authenticators, setAuthenticators] = useState([]);
  // const [showNewForm, setShowNewForm] = useState(false);

  useEffect(() => {
    tenantFunctions.setTitle("Web Authentication");
  }, []);

  const getAuthenticators = async () => {
    const { data } = await axios.get("/api/my-account/webauthn/authenticators");
    setAuthenticators(data.authenticators);
    setUsername(data.username);
  };

  useEffect(() => {
    (async () => {
      await getAuthenticators();
    })();
  }, []);

  const register = useRegistration({
    actionUrl: "/api/my-account/webauthn/register",
    optionsUrl: "/api/my-account/webauthn/challenge",
  });

  // const login = useLogin({
  //   actionUrl: "/api/my-account/webauthn/auth-verify",
  //   optionsUrl: "/api/my-account/webauthn/auth-challenge",
  // });

  const handleRegister = async (ev, formikBag) => {
    try {
      await register({
        username: username,
        passkey_name: ev.name,
      });
      formikBag.resetForm();
      setError(null);
      await getAuthenticators();
    } catch (error) {
      setError(error.message);
    }
  };

  // const handleLogin = async () => {
  //   try {
  //     const response = await login({
  //       username: username,
  //     });
  //     console.log(response);
  //     setError(null);
  //   } catch (error) {
  //     setError(error.message);
  //     console.log("OOPS", error);
  //   }
  // };

  const deleteAuthenticator = async (id) => {
    const response = await axios.post("/api/my-account/webauthn/delete", {
      id
    });

    if (response.data.success) {
      // Successfully deleted
      getAuthenticators();
    } else {
      // Oops
      setError(response.data.message);
    }
  };

  const renderAuthenticators = authenticators.map(item => {
    return (
      <ListGroupItem key={item.id}>
        <div className="row align-items-center">
          <div className="col">
            <p className="mb-0 fw-bold">{item.name || "Unnamed"}</p>
            <p className="mb-0">Created at {item.created_at}</p>
          </div>
          <div className="col-auto">
            <Button variant="secondary" onClick={() => deleteAuthenticator(item.id)}>Delete</Button>
          </div>
        </div>
      </ListGroupItem>
    );
  });

  const crumbs = [
    {
      to: "/my-account",
      title: "My Account",
      name: "My Account",
    },
    {
      to: "/my-account/passkeys",
      title: "Manage your passkeys",
      name: "Passkeys",
    },
  ];

  return (
    <>
      <Header breadcrumbs={<Breadcrumb crumbs={crumbs} />} title="Manage passkeys" subtitle="Passwordless login for your club account" />

      <div className="container-xl">
        <div className="row">
          <div className="col-lg-8">

            {typeof (PublicKeyCredential) === "undefined" &&
              <>
                <Alert variant="warning">
                  We&apos;re sorry. Your web browser does not support the Web Authentication standard required to use passkeys. You&apos;ll need to keep using password based authentication for now.
                </Alert>
              </>
            }

            {typeof (PublicKeyCredential) !== "undefined" &&
              <>
                {error &&
                  <Alert variant="danger" className="mb-3">
                    {error}
                  </Alert>
                }

                <Card className="mb-3">
                  <Card.Header>
                    Add a new passkey
                  </Card.Header>
                  <Card.Body>
                    <Form
                      initialValues={{
                        name: "",
                      }}
                      validationSchema={yup.object({
                        name: yup.string()
                          .required("Please name your passkey so that you can identify the device it is used with"),
                      })}
                      onSubmit={handleRegister}
                      hideClear
                      submitTitle="Add passkey"
                    >
                      <TextInput name="name" label="Passkey name" />
                    </Form>
                  </Card.Body>
                </Card>

                {
                  authenticators.length > 0 &&
                  (<Card className="mb-3">
                    <Card.Header>
                      Current passkeys
                    </Card.Header>
                    <ListGroup variant="flush">
                      {renderAuthenticators}
                    </ListGroup>
                  </Card>)
                }

                {/* {(username && authenticators.length > 0) &&
                  <p>
                    <Button onClick={handleLogin}>Test Login</Button>
                  </p>
                } */}
              </>
            }

          </div>
        </div>
      </div>
    </>
  );

};

export default WebAuthn;