import React from "react";

const Card = (props) => {
  return (
    <div className="overflow-hidden bg-white shadow sm:rounded-lg">
      <div className="px-4 py-5 sm:p-6">{props.children}</div>
    </div>
  );
};

export default Card;
