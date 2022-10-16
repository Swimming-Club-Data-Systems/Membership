import React from "react";
import { Head } from "@inertiajs/inertia-react";
import CentralAuthServices from "@/Layouts/CentralAuthServices";

const Error = (props) => {
    return (
        <CentralAuthServices title={`Error ${props.status}`}>
            <Head title={`Error ${props.status} - ${props.message}`} />

            <p className="text-sm text-gray-600 text-center mb-4">
                {props.message}
            </p>

            <p className="text-xs text-gray-600 text-center">
                &copy; {new Date().getFullYear()} Swimming Club Data Systems
            </p>
        </CentralAuthServices>
    );
};

export default Error;
