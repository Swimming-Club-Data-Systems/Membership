import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";

const Index = (props) => {
    return (
        <>
            <Head title="My Account" />

            <Container noMargin className="py-12"></Container>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title="My Account (Test)"
        subtitle="Manage your personal details"
    >
        {page}
    </MainLayout>
);

export default Index;
