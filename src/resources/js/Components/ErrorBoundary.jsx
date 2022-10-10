import React from "react";
import Container from "../Components/Container";
import ApplicationLogo from "@/Components/ApplicationLogo";

export class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        // Update state so the next render will show the fallback UI.
        if (import.meta.env.PROD) {
            return { hasError: true, error: error };
        }
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
        if (this.state.hasError) {
            // You can render any custom fallback UI

            return (
                <Container>
                    <div className="text-center py-8 max-w-2xl mx-auto">
                        <ApplicationLogo className="mx-auto h-12 mb-8" />
                        <div className="prose max-w-none">
                            <p className="lead">
                                Something went wrong so we are unable to serve
                                you this page. We&apos;re sorry that this has
                                occured.
                            </p>

                            <p>
                                The error occurred in the SCDS Client Side React
                                Application. This error will not be reported
                                automatically.
                            </p>

                            <p>
                                If the issue persists, contact your club for
                                advice and support.
                            </p>

                            <p>Technical Details about the error:</p>

                            <pre className="p-2 mb-4 bg-light">
                                Error: {this.state.error.name}
                                {"\r\n"}
                                Message: {this.state.error.message}
                            </pre>
                        </div>

                        <p className="text-xs text-gray-600">
                            &copy; {new Date().getFullYear()} Swimming Club Data
                            Systems
                        </p>
                    </div>
                </Container>
            );
        }
        return this.props.children;
    };
}
