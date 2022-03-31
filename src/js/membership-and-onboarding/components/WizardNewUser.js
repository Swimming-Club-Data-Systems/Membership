import React, { useEffect, useState } from "react";
import { Tabs, Tab, Button, Alert } from "react-bootstrap";
import Header from "../../components/Header";
import Breadcrumb from "../../components/Breadcrumb";
import * as tenantFunctions from "../../classes/Tenant";
import Container from "../../components/Container";
import Form from "../../components/form/Form";
import * as yup from "yup";
import moment from "moment";
// import TextInput from "../../components/form/TextInput";
// import DateInput from "../../components/form/DateInput";
import Checkbox from "../../components/form/Checkbox";
import * as Config from "./Onboarding.config";
import OnboardingDetails from "./OnboardingDetails";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import { mapDispatchToProps } from "../../reducers/MainStore";
import axios from "axios";
import Link from "../../components/Link";

const WizardNewUser = () => {

  const [members, setMembers] = useState([]);
  const [loaded, setLoaded] = useState(false);

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

  return (
    <>

      {
        loaded &&
        <>
          {
            members.length > 0 &&
            <>
              Members to display
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