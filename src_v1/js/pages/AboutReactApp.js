import React from "react";
import Header from "../components/Header";
import * as tenantFunctions from "../classes/Tenant";
import * as userFunctions from "../classes/User";
import Card from "react-bootstrap/Card";

const AboutReactApp = () => {

  tenantFunctions.setTitle("About this software");

  return (

    <>

      <Header title="About this system" subtitle="This software is written by Swimming Club Data Systems." />

      <div className="container-xl">
        <div className="row">
          <div className="col-lg-8">
            <p className="lead">
              This is the Membership System ReactJS app.
            </p>

            <p>
              The membership system consists of legacy server rendered HTML pages and a modern ReactJS based single page application. This page gives information about the application which you may need to give to {tenantFunctions.getKey("club_name")} or SCDS if you have an issue.
            </p>

            <p>
              Our new ReactJS based application and forms will slowly replace our legacy server rendered HTML pages.
            </p>

            <p>
              This application is licensed to {tenantFunctions.getKey("club_name")} by SCDS. <a href="https://myswimmingclub.uk">Find out about using this application at your club.</a>
            </p>

            <h2>
              Getting help
            </h2>

            <Card body className="mb-3">
              <Card.Title>Contact {tenantFunctions.getKey("club_name")} in the first instance</Card.Title>
              <dl className="row mb-0">
                <dt className="col-sm-3">
                  Email
                </dt>
                <dd className="col-sm-9 mb-0">
                  <a href={"mailto:" + tenantFunctions.getName()}>{tenantFunctions.getName()}</a>
                </dd>
              </dl>
            </Card>

            <Card body className="mb-3">
              <Card.Title>If further help is required, contact SCDS</Card.Title>
              <dl className="row mb-0">
                <dt className="col-sm-3">
                  Email
                </dt>
                <dd className="col-sm-9 mb-0">
                  <a href="mailto:support@myswimmingclub.uk">support@myswimmingclub.uk</a>
                </dd>
              </dl>
            </Card>

            <h2>Support information</h2>
            <p className="lead">
              If we&apos;ve asked for your tenant details so that we can solve a problem, please send us the following:
            </p>

            <Card body className="mb-3">
              <dl className="row mb-0">
                <dt className="col-sm-3">
                  Tenant
                </dt>
                <dd className="col-sm-9">
                  {tenantFunctions.getUuid()}
                </dd>

                <dt className="col-sm-3">
                  Tenant ID
                </dt>
                <dd className="col-sm-9">
                  {tenantFunctions.getId()}
                </dd>

                <dt className="col-sm-3">
                  Tenant Name
                </dt>
                <dd className="col-sm-9">
                  {tenantFunctions.getName()}
                </dd>

                <dt className="col-sm-3">
                  Tenant Code
                </dt>
                <dd className="col-sm-9">
                  {tenantFunctions.getCode()}
                </dd>

                <dt className="col-sm-3">
                  User
                </dt>
                <dd className="col-sm-9 mb-0">
                  {userFunctions.getId() || "You are not logged in"}
                </dd>
              </dl>
            </Card>

            <h2>Features</h2>
            <p className="lead">
              Features include;
            </p>

            <h3>Automatic Member Management</h3>
            <p>
              The application is built on a database of club members. Members are assigned to squads and parents can link swimmers to their account. This allows us to automatically calculate monthly fees and other things.
            </p>

            <h3>Online Gala Entries</h3>
            <p>
              Galas are added to the system by admins. Parents can enter their children into swims by selecting their name, gala and swims. This cuts down on duplicated data from existing arrangements. Parents receive emails detailing their entries.
            </p>

            <h3>Online Attendance Records</h3>
            <p>
              Attendance records are online, facilitating automatic attendance calculation. Squads are managed online and swimmer moves between squads can be scheduled in the system.
            </p>

            <h3>Notify</h3>
            <p>
              Notify is our E-Mail mailing list solution. Administrators can send emails to selected groups of parents for each squad. The system is GDPR compliant and users can opt in or out of receiving emails at any time.
            </p>

            <h3>Direct Debit Payments</h3>
            <p>
              This application has been integrated with GoCardless and their APIs to allow Test Club to bill members by Direct Debit. The GoCardless client library which is included in this software is copyright of GoCardless.
            </p>

            <h2>Legal</h2>
            <p>
              This product includes GeoLite2 data created by MaxMind, available from <a href="https://www.maxmind.com">https://www.maxmind.com</a>.
            </p>

          </div>
        </div>
      </div>

    </>
  );
};

export default AboutReactApp;