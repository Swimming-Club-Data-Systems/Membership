
import React, { useEffect } from "react";
import * as tenantFunctions from "../classes/Tenant";
import Login from "./Login";
import TwoFactor from "./TwoFactor";
import { connect } from "react-redux";
import { mapStateToProps, mapDispatchToProps } from "../reducers/Login";

const LoginPage = (props) => {

  useEffect(() => {
    props.setType("login");

    tenantFunctions.setTitle("Login");

    return () => {
      // props.dispatch
    };
  }, []);

  return (

    <>

      {props.login_page_type === "login" &&
        <Login />
      }

      {props.login_page_type === "twoFactor" &&
        <TwoFactor />
      }

    </>
  );
};

export default connect(mapStateToProps, mapDispatchToProps)(LoginPage);