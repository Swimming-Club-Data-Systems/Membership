import React from "react";
import { Link as InertiaLink } from "@inertiajs/inertia-react";

const Link = ({className, ...props}) => {
  return (
    <InertiaLink
      className={`text-indigo-600 hover:text-indigo-700 hover:underline ${
        className || ""
      }`}
      {...props}
    />
  );
};

export default Link;
