import React, { useEffect } from "react";
// import { Tab, Row, Col, Nav } from "react-bootstrap";
import Header from "../../components/Header";
import Breadcrumb from "../../components/Breadcrumb";
import * as tenantFunctions from "../../classes/Tenant";
import Container from "../../components/Container";
import Form from "../../components/form/Form";
import * as yup from "yup";
import moment from "moment";

const BaseOnboardingSessionForm = () => {

  const crumbs = [
    {
      to: "/onboarding",
      title: "Onboarding",
      name: "Onboarding",
    },
  ];

  useEffect(() => {
    tenantFunctions.setTitle("Onboarding Base Form");
  }, []);

  const submit = () => {

  };

  return (
    <>
      <Header title="Onbarding Base Form" subtitle="Onboarding is the replacement for assisted registration." breadcrumbs={<Breadcrumb crumbs={crumbs} />} />

      <Container>
        <Form
          initialValues={{
            startDate: moment().format("YYYY-MM-DD"),
            chargeFees: false,
            proRata: false,
            welcomeText: "",
            status: "",
            hasDueDate: false,
            dueDate: moment().format("YYYY-MM-DD"),
          }}
          validationSchema={yup.object({
            startDate: yup.date().required("You must enter a date").min("2000-01-01", "You must enter a date greater than 1 January 2000"),
          })}
          onSubmit={submit}
          submitTitle="Save onboarding session"
        >



          {/* <Tab.Container id="left-tabs-example" defaultActiveKey="first">
            <Row>
              <Col sm={3}>
                <Nav variant="pills" className="flex-column">
                  <Nav.Item>
                    <Nav.Link eventKey="first">Tab 1</Nav.Link>
                  </Nav.Item>
                  <Nav.Item>
                    <Nav.Link eventKey="second">Tab 2</Nav.Link>
                  </Nav.Item>
                </Nav>
              </Col>
              <Col sm={9}>
                <Tab.Content>
                  <Tab.Pane eventKey="first">
                    <p>Yo</p>
                  </Tab.Pane>
                  <Tab.Pane eventKey="second">
                    <p>Blah</p>
                  </Tab.Pane>
                </Tab.Content>
              </Col>
            </Row>
          </Tab.Container> */}
        </Form>
      </Container>

    </>
  );
};

export default BaseOnboardingSessionForm;