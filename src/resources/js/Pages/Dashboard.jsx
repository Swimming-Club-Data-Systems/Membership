import React from "react";
import { Head, InertiaLink } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Button from "@/Components/Button";
import { Inertia } from "@inertiajs/inertia";
import { format, formatISO9075, parse } from "date-fns";
import BaseLink from "@/Components/BaseLink";

const Card = (props) => {
    return (
        <div
            // key={person.email}
            className="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:border-gray-400"
        >
            {props.image && (
                <div className="flex-shrink-0">
                    <img
                        className="h-10 w-10 rounded-full"
                        src={props.image}
                        alt={props.imageAlt || ""}
                    />
                </div>
            )}
            <div className="min-w-0 flex-1">
                <BaseLink
                    href={props.link}
                    external={props.external}
                    className="focus:outline-none"
                >
                    <span className="absolute inset-0" aria-hidden="true" />
                    <p className="text-sm font-medium text-gray-900">
                        {props.name}
                    </p>
                    <p className="truncate text-sm text-gray-500 mt-1">
                        {props.role}
                    </p>
                </BaseLink>
            </div>
        </div>
    );
};

const Dashboard = (props) => {
    const date = formatISO9075(new Date(), { representation: "date" });

    return (
        <>
            <Head title="Dashboard" />

            <Container>
                <div className="grid gap-y-8">
                    <div
                        className={`bg-gradient-to-r text-white rounded-lg p-6 from-violet-500 to-fuchsia-500 shadow`}
                    >
                        <h2 className="font-bold text-xl mb-1">
                            Welcome to the revamped membership system!
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

                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                            link={route(
                                                "members.show",
                                                member.MemberID
                                            )}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {props.sessions.length > 0 && (
                        <div id="members">
                            <h2 className="text-xl font-bold text-gray-900 mb-4">
                                Current training sessions
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {props.sessions.map((session) => {
                                    // const squadNames = member.squads.map(
                                    //     (squad) => squad.SquadName
                                    // );

                                    return (
                                        <Card
                                            key={session.SessionID}
                                            name={`${session.SessionName}`}
                                            role={`${format(
                                                Date.parse(
                                                    session.StartDateTime
                                                ),
                                                "HH:mm"
                                            )} - ${format(
                                                Date.parse(session.EndDateTime),
                                                "HH:mm"
                                            )}`}
                                            id={session.SessionID}
                                            link={`/attendance/register?date=${date}&session=${session.SessionID}`}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {props.swim_england_news.length > 0 && (
                        <div id="members">
                            <h2 className="text-xl font-bold text-gray-900 mb-4">
                                Swim England News
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {props.swim_england_news.map((item) => {
                                    // const squadNames = member.squads.map(
                                    //     (squad) => squad.SquadName
                                    // );

                                    return (
                                        <Card
                                            key={item.id}
                                            name={`${item.title}`}
                                            role={format(
                                                Date.parse(item.date),
                                                "HH:mm, do MMMM yyyy"
                                            )}
                                            id={item.id}
                                            link={item.link}
                                            external
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {props.regional_news.length > 0 && (
                        <div id="members">
                            <h2 className="text-xl font-bold text-gray-900 mb-4">
                                Swim England North East News
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {props.regional_news.map((item) => {
                                    // const squadNames = member.squads.map(
                                    //     (squad) => squad.SquadName
                                    // );

                                    return (
                                        <Card
                                            key={item.id}
                                            name={`${item.title}`}
                                            role={format(
                                                Date.parse(item.date),
                                                "HH:mm, do MMMM yyyy"
                                            )}
                                            id={item.id}
                                            link={item.link}
                                            external
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
