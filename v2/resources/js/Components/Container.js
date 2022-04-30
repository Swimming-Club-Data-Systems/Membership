import React from "react";

const Container = (props) => {
  return (
    // <div className="bg-gray-100 dark:bg-slate-900 dark:text-gray-100">
    <div className={`px-3 py-3 sm:container sm:mx-auto ${props.className}`}>
      {props.children}
    </div>
    // </div>
  );
};

export default Container;
