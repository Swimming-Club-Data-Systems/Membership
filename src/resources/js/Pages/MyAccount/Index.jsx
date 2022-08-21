import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";
import InternalContainer from "@/Components/InternalContainer";
import Container from "@/Components/Container";

const Index = (props) => {
    return (
        <>
            <Head title="My Account" />

            <Container noMargin className="py-12">
                
            </Container>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title="My Account"
        subtitle="Manage your personal details"
        children={page}
    />
);

export default Index;
