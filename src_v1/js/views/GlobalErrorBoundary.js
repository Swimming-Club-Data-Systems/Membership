import React from "react";

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
        <div className="container-md">
          <div className="row">
            <div className="col-lg-8 my-5">

              <img className="mb-5" src="/img/corporate/scds.png" width="50" />

              <h1>Oops, something went wrong</h1>
              <p className="lead">Something went wrong so we are unable to serve you this page. We&apos;re sorry that this has occured.</p>

              <p>
                The error occurred in the SCDS Client Side React Application. This error will not be reported automatically.
              </p>

              <p>
                If the issue persists, contact your club for advice and support.
              </p>

              <div className="card card-body mb-3">
                <h2>Technical Details</h2>

                <pre className="p-2 mb-0 bg-light">
                  Error: {this.state.error.name}{"\r\n"}
                  Message: {this.state.error.message}
                </pre>
              </div>

              <p>
                &copy; Swimming Club Data Systems
              </p>

            </div>
          </div>
        </div>
      );
    }
    return this.props.children;
  };
}
