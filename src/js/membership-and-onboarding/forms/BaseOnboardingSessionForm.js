import React, { useEffect, useState } from "react";
import { Tabs, Tab } from "react-bootstrap";
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

const BaseOnboardingSessionForm = () => {

  const crumbs = [
    {
      to: "/onboarding",
      title: "Onboarding",
      name: "Onboarding",
    },
    {
      to: "/onboarding/new",
      title: "Amend",
      name: "Amend",
    },
  ];

  useEffect(() => {
    tenantFunctions.setTitle("Onboarding Base Form");
  }, []);

  const submit = () => {

  };

  const stagesCheckboxes = Object.entries(Config.OnboardingStages).map(([stage, name]) => {
    return (
      <Checkbox
        label={name}
        name={`stages.${stage}`}
        key={stage}
        mb="mb-0"
      />
    );
  });

  const [key, setKey] = useState("details");

  return (
    <>
      <Header title="Onboarding Base Form" subtitle="Onboarding is the replacement for assisted registration." breadcrumbs={<Breadcrumb crumbs={crumbs} />} />

      <Container>
        <Form
          initialValues={{
            memberParentName: "",
            onboardingId: "",
            batchId: "",
            startDate: moment().format("YYYY-MM-DD"),
            chargeFees: false,
            proRata: false,
            welcomeText: "",
            status: "",
            hasDueDate: false,
            dueDate: moment().format("YYYY-MM-DD"),
            stages: {
              account_details: true,
              address_details: true,
              communications_options: true,
              emergency_contacts: true,
              member_forms: true,
              parent_conduct: true,
              data_privacy_agreement: true,
              terms_agreement: true,
              direct_debit_mandate: true,
              fees: true,
            },
            paymentMethods: {
              card: true,
              directDebit: false,
            }
          }}
          validationSchema={yup.object({
            startDate: yup.date().required("You must enter a date").min("2000-01-01", "You must enter a date greater than 1 January 2000"),
          })}
          onSubmit={submit}
          submitTitle="Save onboarding session"
        >

          <Tabs
            id="controlled-tab-example"
            activeKey={key}
            onSelect={(k) => setKey(k)}
            className="mb-3"
          >
            <Tab eventKey="details" title="Details">
              <div className="row">
                <div className="col-lg">
                  <OnboardingDetails />

                  {/* const [field, meta] = useField(props); */}
                </div>
                <div className="col-lg">
                  <h2>Members</h2>
                </div>
              </div>
            </Tab>
            <Tab eventKey="paymentMethods" title="Payment Methods">
              <h2>Supported Payment Methods</h2>
              <Checkbox
                label="Card"
                name="paymentMethods.card"
                mb="mb-0"
              />

              <Checkbox
                label="Direct Debit"
                name="paymentMethods.directDebit"
                mb="mb-0"
              />
            </Tab>
            <Tab eventKey="tasks" title="Tasks">
              <h2>Required Tasks</h2>
              {stagesCheckboxes}
            </Tab>
          </Tabs>
        </Form>
      </Container>

    </>
  );
};

export default BaseOnboardingSessionForm;