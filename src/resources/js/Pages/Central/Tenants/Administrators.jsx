import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import Link from "@/Components/Link";
import FlashAlert from "@/Components/FlashAlert";
import Form from "@/Components/Form/Form";

const Index = (props) => {
    return (
        <>
            <Head title={`Stripe Account - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card
                    title="Tenant Administrators"
                    subtitle="Choose who has access to SCDS System Administration."
                >
                    <FlashAlert className="mb-4" />

                    <div>
                        <p className="text-sm">
                            While most settings for your club membership system
                            can be managed inside the application, billing and
                            your Stripe account can only be managed here in SCDS
                            System Administration.
                        </p>
                    </div>
                </Card>
            </div>
        </>
    );
};

Index.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout children={page} />
    </CentralMainLayout>
);

export default Index;
