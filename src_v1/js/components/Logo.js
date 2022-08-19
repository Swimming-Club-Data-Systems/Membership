import React from "react";
import * as tenantFunctions from "../classes/Tenant";

const Logo = () => {
  return (
    <>
      {(tenantFunctions.getKey("logo_dir")) ? (
        <img src={tenantFunctions.getLogoUrl("logo-75.png")} srcSet={`${tenantFunctions.getLogoUrl("logo-75@2x.png")} 2x, ${tenantFunctions.getLogoUrl("logo-75@3x.png")} 3x`} alt="" className="img-fluid" />
      )
        : (
          <img src="/img/corporate/scds.png" height="75" width="75" alt="" className="img-fluid" />
        )}
    </>
  );
};

export default Logo;