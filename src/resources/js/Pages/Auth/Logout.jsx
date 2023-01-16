import React from "react";
import Button from "@/Components/Button";
import AuthServices from "@/Layouts/AuthServices";
import { Head, useForm } from "@inertiajs/react";
import Link from "@/Components/Link";

export default function Logout() {
    const { post, processing } = useForm();

    const submit = (e) => {
        e.preventDefault();

        post(route("logout"));
    };

    return (
        <AuthServices title="Confirm Sign Out">
            <Head title="Confirm Sign Out" />

            <div className="mb-4 text-sm text-gray-600">
                Please confirm that you wish to sign out.
            </div>

            <form onSubmit={submit}>
                <div className="mt-4 flex items-center justify-between">
                    <Button className="w-full" processing={processing}>
                        Confirm
                    </Button>
                </div>
            </form>
        </AuthServices>
    );
}
