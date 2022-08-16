import React from "react";
import Guest from "@/Layouts/Guest";
import { Head } from "@inertiajs/inertia-react";

export default function V1(props) {
    return (
        <Guest errors={props.errors}>
            <Head title="Oops - You should have reached our V1 App" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <p className="mb-4">
                        Oops - You should have reached Version 1 (Legacy) of our
                        Application but have landed here in Version 2 (SCDSNext)
                        where the path you were looking for doesn't exist yet.
                    </p>

                    <p>Please try reloading the page.</p>
                </div>
            </div>
        </Guest>
    );
}
