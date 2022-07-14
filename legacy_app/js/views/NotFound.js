import React, { useEffect } from "react";
import { useLocation } from "react-router-dom";
import Card from "../components/Card";
import Authenticated from "../layouts/Tenant/Authenticated";
import { ExclamationIcon } from "@heroicons/react/solid";

const NotFound = ({redirect}) => {

  const location = useLocation();

  useEffect(() => {
    document.title = "Error 404";

    if (location.state && location.state.global_questionable_link || redirect) {
      // Link does not exist in React Router but may exist in legacy app so redirect to current url
      window.location.replace(location.pathname);
    }
  }, []);

  const goBack = () => {
    history.back(1);
  };

  return (
    <Authenticated
    // title="The page you requested cannot be found"
    >

      <Card>
        <div className="sm:flex sm:items-start">
          <div className="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
            <ExclamationIcon className="h-6 w-6 text-red-600" aria-hidden="true" />
          </div>
          <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h1 className="text-lg leading-6 font-medium text-gray-900">
              Page not found
            </h1>
            <div className="mt-2">
              <p className="text-sm mb-2 text-gray-500">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. You may also not be authorised to view the page.
              </p>

              <hr className="text-gray-500 mb-2" />

              <p className="text-sm mb-2 text-gray-500">Please try the following: </p>

              <ul className="list-disc list-outside text-sm mb-2 text-gray-500">
                <li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted
                  correctly.</li>
                <li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link
                  is incorrectly formatted.</li>
                <li>Click the <a onClick={goBack} href="#" className="text-indigo-600 hover:text-indigo-700 hover:underline">Back</a> button to try another link.</li>
              </ul>
            </div>
          </div>
        </div>
      </Card>
    </Authenticated>
  );
};

export default NotFound;