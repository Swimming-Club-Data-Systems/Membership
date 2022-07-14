import React from "react";
import Container from "./Container";

const Header = (props) => {
  return (
    <div className="bg-gray-200 text-gray-700  dark:bg-slate-900 dark:text-slate-200">
      <Container>
        <div className="py-3">
          {/* <ApplicationLogo /> */}
          <h1 className="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
            {props.title}
          </h1>
          {props.subtitle && (
            <p className="text-2xl font-bold">{props.subtitle}</p>
          )}
        </div>
      </Container>
    </div>
  );
};

export default Header;
