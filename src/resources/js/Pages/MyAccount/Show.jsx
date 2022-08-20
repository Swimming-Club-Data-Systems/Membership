import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";
import InternalContainer from "@/Components/InternalContainer";
import Container from "@/Components/Container";

const Show = (props) => {
    return (
        <MainLayout
            title="My Account"
            subtitle="Manage your personal details"
        >
            <Head title="My Account" />

            <Container noMargin className="py-12">
                
            </Container>
        </MainLayout>
    );
};

export default Show;
