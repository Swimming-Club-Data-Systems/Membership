import React from "react";

const Card = (props) => {
  return (
    <div
      className={`rounded-lg bg-white px-4 py-3 shadow sm:px-6 ${props.className}`}
    >
      {props.children}
    </div>
  );
};

export default Card;
