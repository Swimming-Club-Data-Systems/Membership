import React from "react";
import { Head, InertiaLink } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/Button";
import { Inertia } from "@inertiajs/inertia";

const Card = (props) => {
    return (
        <div
            // key={person.email}
            className="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:border-gray-400"
        >
            {/*<div className="flex-shrink-0">*/}
            {/*    <img*/}
            {/*        className="h-10 w-10 rounded-full"*/}
            {/*        src={person.imageUrl}*/}
            {/*        alt=""*/}
            {/*    />*/}
            {/*</div>*/}
            <div className="min-w-0 flex-1">
                <InertiaLink
                    href={route("members.show", props.id)}
                    className="focus:outline-none"
                >
                    <span className="absolute inset-0" aria-hidden="true" />
                    <p className="text-sm font-medium text-gray-900">
                        {props.name}
                    </p>
                    <p className="truncate text-sm text-gray-500">
                        {props.role}
                    </p>
                </InertiaLink>
            </div>
        </div>
    );
};

const Dashboard = (props) => {
    return (
        <>
            <Head title="Dashboard" />

            <Container>
                <div className="grid gap-y-8">
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

                    {props.members.length > 0 && (
                        <div id="members">
                            <h2 className="text-xl font-bold text-gray-900 mb-4">
                                Members
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                                {props.members.map((member) => {
                                    const squadNames = member.squads.map(
                                        (squad) => squad.SquadName
                                    );

                                    return (
                                        <Card
                                            key={member.MemberID}
                                            name={`${member.MForename} ${member.MSurname}`}
                                            role={
                                                squadNames.length > 0
                                                    ? squadNames.join(", ")
                                                    : "No Squads"
                                            }
                                            id={member.MemberID}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
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
