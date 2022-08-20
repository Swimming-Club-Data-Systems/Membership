import React from "react";

export default function TenantLogo({ className }) {
  if (className) {
    className = className + " rounded"
  }
  return <img src="/img/corporate/scds.svg" width="50px" className={className} />;
}
