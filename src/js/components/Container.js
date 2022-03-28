import React from "react";

const Container = (props) => {

  let containerType = "container-lg";

  return (
    <div className={containerType}>
      {props.children}
    </div>
  );
};

export default Container;