import React, { useEffect } from "react";
import { useLocation } from "react-router-dom";

const NotFound = () => {

  const location = useLocation();

  useEffect(() => {
    document.title = "Error 404";

    if (location.state && location.state.global_questionable_link) {
      // Link does not exist in React Router but may exist in legacy app so redirect to current url
      window.location.replace(location.pathname);
    }
  }, []);

  const goBack = () => {
    history.back(1);
  };

  return (
    <div className="container-xl">
      <div className="row">
        <div className="col-lg-8">
          <h1>The page you requested cannot be found</h1>

          <p className="lead">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. You may also not be authorised to view the page.
          </p>

          <hr />
          <p>Please try the following: </p>
          <ul>
            <li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted
              correctly.</li>
            <li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link
              is incorrectly formatted.</li>
            <li>Click the <a onClick={goBack} href="#">Back</a> button to try another link.</li>
          </ul>
          <p>HTTP Error 404 - File or directory not found.</p>
          <hr />

          <p className="mt-2">
            <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.
          </p>

          <p>Rendered by the SCDS Membership React Application</p>

        </div>
      </div>
    </div>
  );
};

export default NotFound;