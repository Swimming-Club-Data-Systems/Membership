import React from "react";

const Fieldset = (props) => {
  return (
    <fieldset>
      {props.legend && (
        <legend className="text-base font-medium text-gray-900">
          {props.legend}
        </legend>
      )}
      <div className="mt-4 space-y-4">{props.children}</div>
    </fieldset>
  );
};

export default Fieldset;
