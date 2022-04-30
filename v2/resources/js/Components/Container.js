import React from "react";

const Container = (props) => {
  return (
    // <div className="bg-gray-100 dark:bg-slate-900 dark:text-gray-100">
    <div className="container mx-auto px-3 py-3 sm:px-0">{props.children}</div>
    // </div>
  );
};

export default Container;
