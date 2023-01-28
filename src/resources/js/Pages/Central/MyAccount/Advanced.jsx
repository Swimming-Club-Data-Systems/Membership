import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/react";
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
    <CentralMainLayout
        title="My Account"
        subtitle="Manage your personal details"
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Advanced;
