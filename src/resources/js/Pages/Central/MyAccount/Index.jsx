import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
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
    <CentralMainLayout
        title="My Account"
        subtitle="Manage your personal details"
    >
        {page}
    </CentralMainLayout>
);

export default Index;
