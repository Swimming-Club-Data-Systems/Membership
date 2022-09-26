import React from "react";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/Button";
import { Inertia } from "@inertiajs/inertia";

const Dashboard = (props) => {
    return (
        <>
            <Head title="Dashboard" />

            <Container>
                <div className="grid gap-y-4">
                    <div
                        className={`bg-gradient-to-r text-white rounded-lg p-6 from-violet-500 to-fuchsia-500 shadow`}
                    >
                        <h2 className="font-bold text-xl mb-1">
                            Welcome to SCDS Next!
                        </h2>
                        <p className="font-semibold text-lg mb-4">
                            Things look a little bit different around here.
                        </p>

                        <p>
                            <Button
                                onClick={() => {
                                    Inertia.get(route("about_changes"));
                                }}
                            >
                                Find out why
                            </Button>
                        </p>
                    </div>
                </div>
            </Container>
        </>
    );
};

Dashboard.layout = (page) => (
    <MainLayout
        title={`Hello ${page.props.auth.user.Forename}`}
        subtitle={`Welcome to the ${page.props.tenant.name} Membership System`}
    >
        {page}
    </MainLayout>
);

export default Dashboard;
