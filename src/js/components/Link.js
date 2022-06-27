import React from "react";
import { Link as RouterLink } from "react-router-dom";

const Link = ({className, ...props}) => {
  return (
    <RouterLink
      className={`text-indigo-600 hover:text-indigo-700 hover:underline ${
        className || ""
      }`}
      {...props}
    />
  );
};

export default Link;
