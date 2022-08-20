import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";
import InternalContainer from "@/Components/InternalContainer";
import Container from "@/Components/Container";

const Dev = (props) => {
    return (
        <MainLayout
            title="Developer"
            subtitle="Welcome to the SCDS Next Developer Homepage"
        >
            <Head title="Development" />

            <Container noMargin className="py-12">
                <Card className="mb-4">
                    <div className="prose prose-sm">
                        <h2>About the SCDS Next system</h2>
                        <p>
                            <strong>SCDS Next</strong> is the next generation
                            version of the SCDS Membership System which will
                            slowly replace the existing software, which has
                            retrospectively been renamed{" "}
                            <strong>SCDS V1</strong>.
                        </p>

                        <p>
                            SCDS Next moves the project forward forward on a
                            range of grounds;
                        </p>

                        <ol>
                            <li>Upgrading from <strong>PHP 7.4</strong> to <strong>PHP 8.1</strong></li>
                            <li>Building on top of the hugely popular Laravel framework, replacing the small and lightweight framework previously used, which was no longer supported</li>
                            <li>Adopting modern coding standards and practices which will benefit security, maintainability and speed of development</li>
                            <li>Better support for Queues, WebSockets and a stronger authentication and authorisation model</li>
                            <li>Introduction of automated software tests</li>
                        </ol>
                    </div>
                </Card>

                <Card>
                    <a
                        className="text-indigo-600"
                        href="http://localhost:8025/"
                    >
                        Mailhog
                    </a>
                </Card>
            </Container>
        </MainLayout>
    );
};

export default Dev;
