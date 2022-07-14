import React from "react";
import Placeholder from "react-bootstrap/Placeholder";

const Loader = (props) => {

  return (
    <>
      {
        !props.loaded && (
          <>
            <Placeholder xs={6} animation="glow" />
            <Placeholder className="w-75" animation="glow" /> <Placeholder className="w-25" animation="glow" />
          </>
        )
      }
      {
        props.loaded && props.children
      }
    </>
  );
};

export default Loader;