import React from "react";
import Header from "../../components/Header";
import * as tenantFunctions from "../../classes/Tenant";
import * as userFunctions from "../../classes/User";
import Breadcrumb from "../../components/Breadcrumb";
import { Link } from "react-router-dom";

const Home = () => {

  tenantFunctions.setTitle("Notify");

  const crumbs = [
    {
      to: "/notify",
      title: "Notify",
      name: "Notify",
    },
  ];

  return (

    <>

      <Header breadcrumbs={<Breadcrumb crumbs={crumbs} />} title="Notify" subtitle="Notify is our GDPR Compliant email system for contacting parents and users."/>

      <div className="container-xl">
        <div className="row">

          {userFunctions.hasPermissions(["Admin", "Galas", "Coach"]) &&
            <>

              <div className="col-lg-8">

                <h2>How to Use Notify</h2>
                <p>
                  Notify is available to squad coaches, system administrators and and squad reps. It allows you to send an email to parents/account holders of selected groups of members. It is possible to create a custom group of members (called a Targeted List), or to use a squad or a gala. You can send to any combination of squads and lists just by ticking boxes.
                </p>

                <p>
                  Click on &quot;Notify Composer&quot; to write an email message. All emails sent will be personally addressed to each user who reciceves them.
                </p>

                <p>
                  Emails are added to a queue to be sent. Emails will be sent to all users almost instantaneously. Users who have not opted in to recieving emails will not receive messages.
                </p>

                <p>
                  <strong>Please be aware:</strong> If you Force Send an email, you will be contacted by your System Administrator and asked to justify why you did so. This is because of our obligations under the GDPR rules. Acceptable use of Force Send includes;
                </p>

                <ul>
                  <li>Alerting users that sessions have been cancelled</li>
                  <li>Sending important gala updates</li>
                  <li>Contacting squad users in an emergency</li>
                </ul>

                <p>
                  Other use cases are allowed but must be justifiable in terms of operational needs.
                </p>

                <p className="small">
                  Provided by Swimming Club Data Systems to {tenantFunctions.getName()}.
                </p>

              </div>

              <div className="col">
                <div className="position-sticky top-3 card mb-3">
                  <div className="card-header">Notify tools</div>
                  <div className="list-group list-group-flush ">
                    {/* <a href="/notify" title="Help and information about notify" className="list-group-item list-group-item-action active">
                      Notify home
                    </a> */}
                    <Link to="/notify" title="Help and information about notify" className="list-group-item list-group-item-action active">
                      Notify home
                    </Link>
                    <Link to="/notify/new" title="Send an email to groups of users" className="list-group-item list-group-item-action">
                      New email
                    </Link>
                    {/* <a href="uk/notify/new" title="Send an email to groups of users" className="list-group-item list-group-item-action">
                      New email
                    </a> */}
                    <a href="/notify/history" title="Previously sent notify emails" className="list-group-item list-group-item-action">
                      Sent emails
                    </a>
                    <a href="/notify/lists" title="Manage targeted lists for notify" className="list-group-item list-group-item-action">
                      Targeted lists
                    </a>
                    <a href="/notify/reply-to" title="Choose the email address used for email replies" className="list-group-item list-group-item-action">
                      Reply-to settings
                    </a>
                    <a href="/notify/sms" title="Get SMS numbers for sendign text messages" className="list-group-item list-group-item-action">
                      SMS lists
                    </a>
                  </div>
                </div>
              </div>
            </>
          }

          {!userFunctions.hasPermissions(["Admin", "Galas", "Coach"]) &&
            <>
              <div className="alert alert-info">
                <p className="mb-0">
                  <strong>Unwanted emails from {tenantFunctions.getName()} or SCDS?</strong>
                </p>
                <p className="mb-0">
                  <a className="alert-link" href="https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u" target="_blank" title="Report email abuse" rel="noreferrer">Report mail abuse</a> and we&apos;ll investigate.
                </p>
              </div>

              <p>
                This General Data Protection Regulation Compliant system enables rapid communication with our members. The system allows us to target emails to parents of selected squads, those who have entered certain galas or those in select groups and supports modern email standards.
              </p>

              <p>
                To unsubscribe or resubscribe to messages sent by Notify, go to <a href="/my-account">My Account</a>. You can also control your SMS Messaging preferences there.
              </p>

              <p>
                Many emails will also come with an unsubscribe link at the end, though mandatory information emails won&apos;t.
              </p>

              <p>
                Please note that occasionally your club may send you an email regardless of your opt-in/opt-out settings if there is a legitimate business purpose behind doing so.
              </p>
            </>
          }
        </div>
      </div>

    </>
  );
};

export default Home;