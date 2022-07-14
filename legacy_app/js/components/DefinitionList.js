import React from "react";

export const Dl = (props) => {
  return (
    <dl className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
      {props.children}
    </dl>
  );
};

export const Dd = (props) => {
  return (
    <dt className="text-sm font-medium text-gray-500">{props.children}</dt>
  );
};

export const Dt = (props) => {
  return <dd className="mt-1 text-sm text-gray-900">{props.children}</dd>;
};
