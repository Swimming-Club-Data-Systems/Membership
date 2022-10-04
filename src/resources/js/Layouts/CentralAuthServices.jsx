import React from "react";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import ApplicationLogo from "@/Components/ApplicationLogo";

const CentralAuthServices = ({ title, children }) => {
    return (
        <div className="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <ApplicationLogo className="mx-auto h-12 w-auto" />
                <h2 className="mt-6 text-center text-3xl tracking-tight font-bold text-gray-900">
                    {title || "Sign in to your account"}
                </h2>
                {/* <p className="mt-2 text-center text-sm text-gray-600">
                    Or{" "}
                    <a
                        href="#"
                        className="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        start your 14-day free trial
                    </a>
                </p> */}
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <Card>
                    <FlashAlert className="mb-4" />
                    {children}
                </Card>
            </div>
        </div>
    );
};

export default CentralAuthServices;
