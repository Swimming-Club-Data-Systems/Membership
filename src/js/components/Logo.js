import React from "react";
import * as tenantFunctions from "../classes/Tenant";
import ApplicationLogo from "./ApplicationLogo";

const Logo = () => {
  return (
    <>
      {(tenantFunctions.getKey("logo_dir")) ? (
        <img src={tenantFunctions.getLogoUrl("logo-75.png")} srcSet={`${tenantFunctions.getLogoUrl("logo-75@2x.png")} 2x, ${tenantFunctions.getLogoUrl("logo-75@3x.png")} 3x`} alt="" className="img-fluid" />
      )
        : (
          <ApplicationLogo />
        )}
    </>
  );
};

export default Logo;