import React from "react";

const Container = (props) => {
  return (
    // <div className="bg-gray-100 dark:bg-slate-900 dark:text-gray-100">
    <div className={`mx-auto lg:px-6 xl:px-8 ${props.className}`}>
      {props.children}
    </div>
    // </div>
  );
};

export default Container;
