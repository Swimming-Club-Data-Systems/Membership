import React from "react";
import AuthServices from "@/Layouts/AuthServices";
import { Head } from "@inertiajs/react";

export default function V1(props) {
    return (
        <AuthServices title="No matched path in V2" errors={props.errors}>
            <Head title="Oops - You should have reached our V1 App" />

            <p className="mb-4 text-sm text-gray-600">
                Oops - You should have reached Version 1 (Legacy) of our
                Application but have landed here in Version 2 (SCDSNext) where
                the path you were looking for doesn't exist yet.
            </p>

            <p className="text-sm text-gray-600">
                Please try reloading the page.
            </p>
        </AuthServices>
    );
}
