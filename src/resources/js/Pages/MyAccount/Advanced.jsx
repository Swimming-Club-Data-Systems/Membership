import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Layout from "./Layout";

const Advanced = (props) => {
    return (
        <>
            <Head title="My Account" />

            <Container noMargin className="py-12"></Container>
        </>
    );
};

Advanced.layout = (page) => (
    <MainLayout title="My Account" subtitle="Manage your personal details">
        <Layout children={page} />
    </MainLayout>
);

export default Advanced;
