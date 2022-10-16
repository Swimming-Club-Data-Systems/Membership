
import React from "react";
import * as tenantFunctions from "../classes/Tenant";
import { connect } from "react-redux";
import Logo from "../components/Logo";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";
import { Link, Outlet } from "react-router-dom";

const titles = {
  login: {
    heading: "Login",
    subheading: "Sign in to " + tenantFunctions.getName(),
  },
  twoFactor: {
    heading: "Confirm it's you",
    subheading: "Enter your two-factor authentication code",
  },
  resetPassword: {
    heading: "Get back into your account",
    subheading: "Find your account or reset your password",
  },
};

const LoginPageWrapper = (props) => {

  return (

    <>

      <div className="container min-vh-100 mb-n3 overflow-auto">
        <div className="row justify-content-center py-3">
          <div className="col-lg-8 col-md-10">

            <p className="mb-5">
              <Link to="/" className="btn btn-outline-primary btn-outline-light-d">Quit</Link>
            </p>

            <div className="row align-items-center justify-content-between">
              <div className="col order-2 order-md-1">
                <h1>{titles[props.login_page_type].heading}</h1>
                <p className="lead mb-0">{titles[props.login_page_type].subheading}</p>
              </div>
              <div className="col-12 col-md-6 order-1 order-md-2 text-md-end">
                <Logo />
                <div className="mb-4 d-md-none"></div>
              </div>
            </div>
            <div className="mb-4 d-md-none"></div>
            <div className="mb-5 d-none d-md-block"></div>

            <Outlet />

            <p>
              Need help? <Link to="/about">Get support from {tenantFunctions.getName()}</Link>.
            </p>

          </div>
        </div>
      </div>

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(LoginPageWrapper);