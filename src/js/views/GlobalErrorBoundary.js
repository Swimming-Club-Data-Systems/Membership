import React from "react";
import ApplicationLogo from "../components/ApplicationLogo";
import InternalContainer from "../components/InternalContainer";
import Logo from "../components/Logo";

export class GlobalErrorBoundary extends React.Component {

  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI.
    return { hasError: true, error: error };
  }

  componentDidCatch(error, errorInfo) {
    // You can also log the error to an error reporting service
    // logErrorToMyService(error, errorInfo);
    document.title = "An error occurred - SCDS";
    console.error(error);
    console.error(errorInfo);
  }

  goBack = () => {
    history.back(1);
  };

  render = () => {
    if (this.state.hasError) {      // You can render any custom fallback UI
      return (
        <div className="my-5 max-w-screen-md px-5 mx-auto">
          <div className="flex my-5">
            <div>
              <ApplicationLogo className="h-12 mr-4" />
            </div>
            <div>
              <Logo className="h-12" />
            </div>
          </div>

          <h1 className="text-indigo-600 text-5xl mb-2">Oops, something went wrong</h1>
          <p className="text-xl mb-4">Something went wrong so we are unable to serve you this page. We&apos;re sorry that this has occured.</p>

          <p className="mb-4">
            The error occurred in the SCDS Client Side React Application. This means that this error will not be reported automatically. If you need to report this error, please send a copy of the technical details below to <a href="mailto:support@myswimmingclub.uk" className="text-indigo-600 hover:text-indigo-700 hover:underline">support@myswimmingclub.uk</a>.
          </p>

          <p className="mb-4">
            If the issue persists, contact your club for advice and support.
          </p>

          <div className="mb-4">
            <h2 className="mb-2">Technical Details</h2>

            <pre className="p-2 mb-0 bg-white overflow-x-scroll">
              Error: {this.state.error.name}{"\r\n"}
              Message: {this.state.error.message}
            </pre>
          </div>

          <p>
            &copy; Swimming Club Data Systems
          </p>

        </div>
      );
    }
    return this.props.children;
  };
}
