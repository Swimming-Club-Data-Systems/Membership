import React from "react";

export default function ApplicationLogo({ className }) {
  if (className) {
    className = className + " rounded"
  }
  return <img src="/img/corporate/scds.png" width="50px" className={className} />;
}
