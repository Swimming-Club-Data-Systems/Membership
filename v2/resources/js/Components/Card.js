import React from "react";

const Card = (props) => {
  return (
    <div className="overflow-hidden bg-white shadow sm:rounded-lg">
      {props.header && (
        <div className="bg-gray-50 px-4 py-4 sm:px-6">{props.header}</div>
      )}
      <div className="px-4 py-5 sm:p-6">{props.children}</div>
      {props.footer && (
        <div className="bg-gray-50 px-4 py-4 sm:px-6">{props.footer}</div>
      )}
    </div>
  );
};

export default Card;
