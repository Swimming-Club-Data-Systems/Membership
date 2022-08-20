import React from "react";
import Template from "@/Layouts/Template";
import { Head } from "@inertiajs/inertia-react";

export default function Dashboard(props) {
    return (
        <Template
            auth={props.auth}
            errors={props.errors}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Development
                </h2>
            }
        >
            <Head title="Development" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <a className="text-indigo-600" href="http://localhost:8025/">Mailhog</a>
                        </div>
                    </div>
                </div>
            </div>
        </Template>
    );
}
