import React from "react";
import { Link, useMatch, useResolvedPath } from "react-router-dom";

const Breadcrumb = (props) => {

  const crumbs = props.crumbs;

  // let resolved = useResolvedPath(to);
  // let match = useMatch({ path: resolved.pathname, end: true });

  if (crumbs.length === 0) {
    // If no links return with nothing
    return;
  }

  // members.map((member, idx) => (
  const breadcrumbs = crumbs.map((crumb) => {
    let resolved = useResolvedPath(crumb.to);
    let match = useMatch({ path: resolved.pathname, end: true });

    if (match) {
      return (
        <li className="breadcrumb-item active" key={crumb.to} title={crumb.title} aria-current="page">
          {crumb.name}
        </li>
      );
    } else {
      return (
        <li className="breadcrumb-item" key={crumb.to}>
          <Link to={crumb.to} title={crumb.title} state={{global_questionable_link: true}}>
            {crumb.name}
          </Link>
        </li>
      );
    }
  });

  return (
    <nav aria-label="breadcrumb">
      <ol className="breadcrumb">
        {breadcrumbs}
      </ol>
    </nav>
  );
};

export default Breadcrumb;