import React, { useEffect, useState } from "react";
import { Tabs, Tab, Button } from "react-bootstrap";
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
import * as Config from "../components/Onboarding.config";
import OnboardingDetails from "../components/OnboardingDetails";
import WizardSelect from "../components/WizardSelect";
import WizardExistingUser from "../components/WizardExistingUser";
import WizardNewUser from "../components/WizardNewUser";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import * as OnboardingIds from "../ids/Onboarding.ids";
import { mapDispatchToProps } from "../../reducers/MainStore";

const OnboardingWizard = (props) => {

  const crumbs = [
    {
      to: "/onboarding",
      title: "Onboarding",
      name: "Onboarding",
    },
    {
      to: "/onboarding/new",
      title: "New",
      name: "New",
    },
  ];

  useEffect(() => {
    tenantFunctions.setTitle("New Onboarding Session");
  }, []);

  const submit = () => {

  };

  return (
    <>
      <Header title="Onboard Members" subtitle="Onboarding is the replacement for assisted registration, membership batches and more." breadcrumbs={<Breadcrumb crumbs={crumbs} />} />

      <Container>
        {props[OnboardingIds.FORM_TO_DISPLAY] === OnboardingIds.SELECT_TYPE &&
          <WizardSelect />
        }

        {props[OnboardingIds.FORM_TO_DISPLAY] === OnboardingIds.ONBOARDING_NEW_USER &&
          <WizardNewUser />
        }

        {props[OnboardingIds.FORM_TO_DISPLAY] === OnboardingIds.ONBOARDING_EXISTING_USER &&
          <WizardExistingUser />
        }
      </Container>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(OnboardingWizard);