import React from "react";
import BaseLink from "./BaseLink";

export default function NavLink({ href, active, children, ...otherProps }) {
  return (
    <BaseLink
      to={href}
      className={
        active
          ? "mb-2 flex w-full rounded bg-gray-100 p-2"
          : "mb-2 flex w-full rounded p-2 transition duration-150 ease-in-out hover:bg-indigo-100 focus:bg-indigo-200"
      }
      {...otherProps}
    >
      {children}
    </BaseLink>
  );
}
