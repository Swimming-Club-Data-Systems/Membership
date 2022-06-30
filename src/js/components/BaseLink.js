import React from "react";
import { Link as RouterLink } from "react-router-dom";

const BaseLink = ({ state, ...props }) => {
  return (
    <RouterLink
      {...props}
      state={{
        ...state,
        global_questionable_link: true,
      }}
    />
  );
};

export default BaseLink;
