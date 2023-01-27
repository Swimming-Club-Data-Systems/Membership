import ReportAnErrorForm from "@/Common/ReportAnErrorForm";
import MainLayout from "@/Layouts/MainLayout";
import Container from "@/Components/Container";
import Card from "@/Components/Card";
import React from "react";
import { Head } from "@inertiajs/react";

const ReportAnError = () => {
    return (
        <Container noMargin>
            <Card>
                <ReportAnErrorForm />
            </Card>
        </Container>
    );
};

ReportAnError.layout = (page) => (
    <MainLayout
        title={`Report an error`}
        subtitle="Reporting issues is the quickest way to get help and support."
    >
        {page}
    </MainLayout>
);

export default ReportAnError;
