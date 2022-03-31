import React from "react";
import { Link as RouterLink } from "react-router-dom";

const Link = (props) => {

  const {
    state,
    ...otherProps
  } = props;

  const updatedState = {
    global_questionable_link: true,
    ...state,
  };

  return (
    <RouterLink {...otherProps} state={updatedState}>
      {props.children}
    </RouterLink>
  );
};

export default Link;