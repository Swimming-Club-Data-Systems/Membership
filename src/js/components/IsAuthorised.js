import React from "react";
import NotFound from "../views/NotFound";
import { hasPermissions } from "../classes/User";

const IsAuthorised = (props) => {

  if (hasPermissions(props.permissions)) {
    return props.children;
  }

  return <NotFound />;
};

export default IsAuthorised;