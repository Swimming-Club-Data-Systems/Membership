import React from "react";

export default function ApplicationLogo({ className }) {
  if (className) {
    className = className + " rounded"
  }
  return <img src="/img/corporate/scds.svg" width="50px" className={className} alt="SCDS" />;
}
