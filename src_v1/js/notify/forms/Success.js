import React from "react";
import Header from "../../components/Header";
import Breadcrumb from "react-bootstrap/Breadcrumb";
import * as tenantFunctions from "../../classes/Tenant";

export class Success extends React.Component {

  constructor() {
    super();
  }

  render() {
    tenantFunctions.setTitle("Success - Notify Composer");

    const breadcrumbs = (
      <Breadcrumb>
        <Breadcrumb.Item href="/notify">Notify</Breadcrumb.Item>
        <Breadcrumb.Item active>Composer</Breadcrumb.Item>
      </Breadcrumb>
    );

    return (
      <>
        <Header title="Send a new email" subtitle="Send emails to targeted groups" breadcrumbs={breadcrumbs} />

        <div className="container-xl">
          <div className="alert alert-success">
            <p className="mb-0">
              <strong>We have successfully sent your email</strong>
            </p>
            <p className="mb-0">
              Thank you for trying the new Notify Composer. We welcome any feedback.
            </p>
          </div>
        </div>

      </>
    );
  }

}

// ReactDOM.render(<App />, document.getElementById('scds-react-root'));
export default Success;
