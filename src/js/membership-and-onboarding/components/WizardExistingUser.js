import React, { useEffect } from "react";
import { Modal, Button } from "react-bootstrap";
import * as tenantFunctions from "../../classes/Tenant";
import { mapStateToProps } from "../../reducers/Onboarding";
import { connect } from "react-redux";
import { mapDispatchToProps } from "../../reducers/MainStore";
import { useNavigate } from "react-router-dom";

const WizardExistingUser = (props) => {

  const navigate = useNavigate();

  useEffect(() => {
    tenantFunctions.setTitle("New Onboarding Session");
  }, []);

  const handleGoToUsers = () => {
    navigate("/users", {
      state: {
        global_questionable_link: true
      }
    });
  };

  return (
    <>

      <Modal show={props.show} onHide={props.handleClose} centered>
        <Modal.Header closeButton>
          <Modal.Title>Existing User</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <p>
            We&apos;re still working on building an exisiting user picker.
          </p>
          <p className="mb-0">
            In the meantime, you can repeat onboarding or add additional memberships for existing users by finding their page in the user directory.
          </p>
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={props.handleClose}>
            Close
          </Button>
          <Button variant="primary" onClick={handleGoToUsers}>
            View Users
          </Button>
        </Modal.Footer>
      </Modal>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(WizardExistingUser);