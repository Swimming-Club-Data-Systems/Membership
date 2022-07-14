import React from "react";

export default function ApplicationLogo({ className }) {
  
  let classes = "h-12";
  if (className) {
    classes = className;
  }

  return <img src="/img/corporate/scds.svg" className={classes} />;
}
