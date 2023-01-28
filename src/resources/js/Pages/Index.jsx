import React from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Link from "@/Components/Link";

const Index = (props) => {
    return (
        <>
            <Head
                title={`Welcome to the ${props.tenant.name} Membership System`}
            />

            <Container>
                <h2 className="text-xl font-bold text-gray-900">
                    Already registered?
                </h2>
                <p className="text-sm font-medium text-gray-500 mb-4">
                    Log into your account now.
                </p>

                <p className="text-sm font-medium text-gray-500 mb-4">
                    <Link href={route("login")}>Login</Link>
                </p>

                <h2 className="text-xl font-bold text-gray-900">
                    Not got an account?
                </h2>
                <p className="text-sm font-medium text-gray-500 mb-4">
                    Your club will create an account for you.
                </p>
                <p className="text-sm font-medium text-gray-500 mb-4">
                    If you&apos;ve just joined, the person handling your
                    application will be in touch with you soon.
                </p>
            </Container>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title={`Welcome to the ${page.props.tenant.name} Membership System`}
        subtitle="Please login to get started"
    >
        {page}
    </MainLayout>
);

export default Index;
