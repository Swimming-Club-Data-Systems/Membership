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
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        {page}
    </CentralMainLayout>
);

export default Index;
