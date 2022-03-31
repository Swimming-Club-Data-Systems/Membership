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
import * as Config from "./Onboarding.config";
import OnboardingDetails from "./OnboardingDetails";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import { mapDispatchToProps } from "../../reducers/MainStore";

const WizardExistingUser = () => {

  useEffect(() => {
    tenantFunctions.setTitle("New Onboarding Session");
  }, []);

  return (
    <>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(WizardExistingUser);