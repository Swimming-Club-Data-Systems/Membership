import React from "react";

const Card = (props) => {
  const { className = "" } = props;

  return (
    <div className={`overflow-hidden bg-white shadow sm:rounded-lg ${className}`}>
      {props.header && (
        <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">{props.header}</div>
      )}
      <div className="bg-white py-6 px-4 space-y-6 sm:p-6">{props.children}</div>
      {props.footer && (
        <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">{props.footer}</div>
      )}
    </div>
  );
};

export default Card;
