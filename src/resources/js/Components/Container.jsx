import React from "react";

const Container = (props) => {

  let classes = "";

  if (!props.noMargin) {
    classes = " px-4 ";
  }

  if (props.className) {
    classes += props.className;
  }

  return (
    // <div className="bg-gray-100 dark:bg-slate-900 dark:text-gray-100">
    <div className={`max-w-7xl mx-auto lg:px-8 sm:px-4 ${classes}`}>
      {props.children}
    </div>
    // </div>
  );
};

export default Container;
