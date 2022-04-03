import React, { useEffect, useState } from "react";
import { Button } from "react-bootstrap";
import * as tenantFunctions from "../../classes/Tenant";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import * as OnboardingIds from "../ids/Onboarding.ids";
import { mapDispatchToProps } from "../../reducers/MainStore";
import WizardExistingUser from "./WizardExistingUser";

const WizardSelect = (props) => {

  const [showExisting, setShowExisting] = useState(false);

  useEffect(() => {
    tenantFunctions.setTitle("New Onboarding Session");
  }, []);

  const goToNew = () => {
    props.setValues({
      [OnboardingIds.FORM_TO_DISPLAY]: OnboardingIds.ONBOARDING_NEW_USER,
    });
  };

  const goToExisting = () => {
    // props.setValues({
    //   [OnboardingIds.FORM_TO_DISPLAY]: OnboardingIds.ONBOARDING_EXISTING_USER,
    // });
    setShowExisting(true);
  };

  const hideExisting = () => {
    setShowExisting(false);
  };

  return (
    <>
      <div className="row">
        <div className="col-lg">
          <div className="card card-body h-100 d-grid">
            <div>
              <h2>Onboard a New Member</h2>
              <p className="lead">
                Create and onboard a new user and associated members
              </p>
            </div>
            <p className="mb-0 mt-auto d-flex">
              <Button variant="primary" onClick={goToNew}>
                Go
              </Button>
            </p>
          </div>
        </div>

        <div className="col-lg">
          <div className="card card-body h-100 d-grid">
            <div>
              <h2>Repeat Onboarding or Add a New Membership</h2>
              <p className="lead">
                Repeat onboarding for an existing user and associated members, or bill them for additional membership fees
              </p>
            </div>
            <p className="mb-0 mt-auto d-flex">
              <Button variant="primary" onClick={goToExisting}>
                Go
              </Button>
            </p>
          </div>
        </div>

      </div>

      <WizardExistingUser
        show={showExisting}
        handleClose={hideExisting}
      />

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(WizardSelect);