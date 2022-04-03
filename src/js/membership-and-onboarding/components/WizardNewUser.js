import React, { useEffect, useState } from "react";
import { Alert } from "react-bootstrap";
import * as tenantFunctions from "../../classes/Tenant";
import Form from "../../components/form/Form";
import * as yup from "yup";
import "yup-phone";
import Checkbox from "../../components/form/Checkbox";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import { mapDispatchToProps } from "../../reducers/MainStore";
import axios from "axios";
import Link from "../../components/Link";
import TextInput from "../../components/form/TextInput";
import { useFormikContext } from "formik";

const CheckUserEmail = (props) => {
  const { values, errors, setFieldValue, validateField } = useFormikContext();
  const { setUserExists } = props;

  useEffect(() => {
    if (!errors.emailAddress) {
      getUserByEmail();
    }
  }, [values.emailAddress]);

  const getUserByEmail = async () => {
    const response = await axios.get("/api/onboarding/check-user", {
      params: {
        email: values.emailAddress,
      }
    });
    if (response.data.user) {
      // Set state
      setFieldValue("firstName", response.data.user.first_name);
      setFieldValue("lastName", response.data.user.last_name);
      setFieldValue("mobileNumber", response.data.user.mobile);
      validateField("firstName");
      validateField("lastName");
      validateField("mobileNumber");
      setUserExists(true);
    } else {
      setFieldValue("firstName", "");
      setFieldValue("lastName", "");
      setFieldValue("mobileNumber", "");
      setUserExists(false);
    }
  };

  return null;
};

const WizardNewUser = () => {

  const [members, setMembers] = useState([]);
  const [loaded, setLoaded] = useState(false);
  const [userExists, setUserExists] = useState(false);

  useEffect(() => {
    tenantFunctions.setTitle("New Onboarding Session");
  }, []);

  useEffect(() => {
    (async () => {
      const response = await axios.get("/api/members/orphans");
      setLoaded(true);
      setMembers(response.data);
    })();
  }, []);

  const submit = (values) => {
    alert(JSON.stringify(values, null, 2));
  };

  const renderMembers = members.map(member => {
    return (
      <Checkbox
        label={`${member.first_name} ${member.last_name}`}
        name="members"
        value={`${member.id}`}
        key={member.id}
      />
    );
  });

  return (
    <>

      {
        loaded &&
        <>
          {
            members.length > 0 &&
            <>
              <Form
                initialValues={{
                  emailAddress: "",
                  firstName: "",
                  lastName: "",
                  mobileNumber: "",
                  members: [],
                }}
                validationSchema={yup.object({
                  emailAddress: yup.string().required("You must enter an email address").email("You must enter a valid email address"),
                  firstName: yup.string().required("You must enter a first name"),
                  lastName: yup.string().required("You must enter a last name"),
                  mobileNumber: yup.string().required("You must enter a mobile number").phone("GB", true, "You must enter a valid mobile number"),
                  members: yup.array().min(1, "You must select at least one member to add to this account")
                })}
                onSubmit={submit}
                submitTitle="Next"
              >
                {/* User details first */}
                <h2>Enter user details</h2>

                <p>
                  Enter the user&apos;s email address here to begin creating a new user. We&apos;ll check to see if an account already exists.
                </p>

                <p>
                  For existing users, you can also start onboarding from their user page.
                </p>

                <TextInput
                  label="User email address"
                  name="emailAddress"
                  type="email"
                />
                <CheckUserEmail setUserExists={setUserExists} />

                {
                  userExists &&
                  <Alert variant="info">
                    <p className="mb-0">
                      <strong>Heads up!</strong>
                    </p>
                    <p>
                      A user with this email address already exists. We&apos;ve prefilled their information and will add your selected members to their account.
                    </p>
                    <p className="mb-0">
                      To create a new user, please enter a different email address.
                    </p>
                  </Alert>
                }

                <div className="row">
                  <div className="col">
                    <TextInput
                      label="First name"
                      name="firstName"
                      disabled={userExists}
                    />
                  </div>

                  <div className="col">
                    <TextInput
                      label="Last name"
                      name="lastName"
                      disabled={userExists}
                    />
                  </div>
                </div>

                <TextInput
                  label="Mobile number"
                  name="mobileNumber"
                  disabled={userExists}
                />

                {/* Then select members */}
                <h2>Select members</h2>

                {renderMembers}

              </Form>
            </>
          }

          {
            members.length === 0 &&
            <>
              <Alert variant="warning">
                <p className="mb-0">
                  <strong>There are no unlinked members at this time</strong>
                </p>
                <p className="mb-0">
                  <Link to="/members/new" className="alert-link">Add a new Member</Link> to begin onboarding or <Link to="/onboarding/new" className="alert-link">onboard an existing user</Link>.
                </p>
              </Alert>
            </>
          }
        </>
      }


    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(WizardNewUser);