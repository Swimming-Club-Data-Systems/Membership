import React from "react";
import BaseLink from "./BaseLink";

const Link = ({className, ...props}) => {
  return (
    <BaseLink
      className={`text-indigo-600 hover:text-indigo-700 hover:underline ${
        className || ""
      }`}
      {...props}
    />
  );
};

export default Link;
