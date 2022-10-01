import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";

const GradientCard = (props) => {
    return (
        <div
            className={`bg-gradient-to-r text-white rounded-lg p-6 ${props.fromColour} ${props.toColour} shadow`}
        >
            <h2 className="font-bold text-2xl mb-1">{props.title}</h2>
            <p className="font-semibold text-lg mb-4">{props.subtitle}</p>

            {props.children}
        </div>
    );
};

const Index = () => {
    return (
        <Container>
            <main className="">
                <div className="sm:mt-2 md:mt-6 lg:mt-10 xl:mt-18 mb-10 sm:mb-12 md:mb-16 lg:mb-20 xl:mb-28 sm:text-center lg:text-left">
                    <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                        <span className="block xl:inline">
                            Software to run your
                        </span>{" "}
                        <span className="block text-gray-900 xl:inline">
                            swimming club
                        </span>
                    </h1>
                    <p className="mt-3 text-base text-gray-500 sm:mx-auto sm:mt-5 sm:max-w-xl sm:text-lg md:mt-5 md:text-xl lg:mx-0">
                        Manage your members, subscriptions, competition entries
                        and more.
                    </p>
                    {/*<div className="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">*/}
                    {/*    <div className="rounded-md shadow">*/}
                    {/*        <a*/}
                    {/*            href="#"*/}
                    {/*            className="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 md:py-4 md:px-10 md:text-lg"*/}
                    {/*        >*/}
                    {/*            Get started*/}
                    {/*        </a>*/}
                    {/*    </div>*/}
                    {/*    <div className="mt-3 sm:mt-0 sm:ml-3">*/}
                    {/*        <a*/}
                    {/*            href="#"*/}
                    {/*            className="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-100 px-8 py-3 text-base font-medium text-indigo-700 hover:bg-indigo-200 md:py-4 md:px-10 md:text-lg"*/}
                    {/*        >*/}
                    {/*            Live demo*/}
                    {/*        </a>*/}
                    {/*    </div>*/}
                    {/*</div>*/}
                </div>

                <div className="grid gap-y-8 gap-x-6 grid-cols-12">
                    <div className="col-span-full lg:col-span-7">
                        <GradientCard
                            fromColour="from-violet-500"
                            toColour="to-fuchsia-500"
                            title="Members and squads"
                            subtitle="Manage your squads and members."
                        >
                            <ul className="list-disc ml-4">
                                <li>
                                    Manage your member&apos;s and their personal
                                    details
                                </li>
                                <li>Assign members to multiple squads</li>
                                <li>Track personal best times</li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7 lg:col-start-6">
                        <GradientCard
                            fromColour="from-red-600"
                            toColour="to-orange-600"
                            title="Member communications"
                            subtitle="Contact your members and parents easily."
                        >
                            <ul className="list-disc ml-4">
                                <li>
                                    Contact parents quickly and easily by email
                                </li>
                                <li>Create custom groups for email messages</li>
                                <li>Get replies to emails you send</li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7">
                        <GradientCard
                            fromColour="from-sky-600"
                            toColour="to-blue-600"
                            title="Automated payments"
                            subtitle="Collect payments by Direct Debit and one-off
                                payments by card."
                        >
                            <ul className="list-disc ml-4">
                                <li>Take payments easily and securely</li>
                                <li>
                                    A white-label experience for card payments
                                </li>
                                <li>
                                    An optional white-label experience for
                                    direct-debit payments
                                </li>
                                <li>
                                    Automated billing - no need to set up plans
                                    for each family or member
                                </li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7 lg:col-start-6">
                        <GradientCard
                            fromColour="from-green-600"
                            toColour="to-emerald-600"
                            title="Online gala entries"
                            subtitle="Your members can enter their competitions online."
                        >
                            <ul className="list-disc ml-4">
                                <li>Fast and secure paperless gala entries</li>
                                <li>Various entry methods</li>
                                <li>Squad rep features</li>
                                <li>
                                    Simple secure payment by card or on account
                                </li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7">
                        <GradientCard
                            fromColour="from-cyan-600"
                            toColour="to-sky-600"
                            title="Online registers"
                            subtitle="Take your registers online and keep your coaches up to date."
                        >
                            <ul className="list-disc ml-4">
                                <li>Up to date attendance information</li>
                                <li>Attendance monitoring</li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7 lg:col-start-6">
                        <GradientCard
                            fromColour="from-indigo-600"
                            toColour="to-violet-600"
                            title="Paperless registration and renewal"
                            subtitle="Banish paper and make registration and renewal easy."
                        >
                            <ul className="list-disc ml-4">
                                <li>Members securely review data</li>
                                <li>No need to refill forms</li>
                                <li>Secure payment for membership fees</li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full lg:col-span-7">
                        <GradientCard
                            fromColour="from-purple-600"
                            toColour="to-fuchsia-600"
                            title="Much more"
                            subtitle="Online photo permissions, medical forms and more."
                        >
                            <ul className="list-disc ml-4">
                                <li>
                                    Custom photo permissions can be updated at
                                    any time
                                </li>
                                <li>Online medical forms for members</li>
                                <li>
                                    Medical and photography information reported
                                    to coaches on registers
                                </li>
                                <li>Printable backup forms</li>
                            </ul>
                        </GradientCard>
                    </div>

                    <div className="col-span-full">
                        <h2 className="font-bold text-2xl mb-1">
                            Used by clubs across the North East
                        </h2>
                        <p className="font-semibold text-lg mb-4">
                            Feature development is driven by the needs of our
                            clubs.
                        </p>

                        <div className="grid gap-x-4 gap-y-8 grid-cols-1 md:grid-cols-5 items-center mt-10">
                            <a
                                href="https://www.rdasc.org.uk/"
                                target="_blank"
                                rel="noreferrer"
                                className="justify-self-center"
                            >
                                <img
                                    src="/img/customer-clubs/rice.png"
                                    className="h-20"
                                />
                            </a>

                            <a
                                href="https://newcastleswimteam.co.uk/"
                                target="_blank"
                                rel="noreferrer"
                                className="justify-self-center"
                            >
                                <img
                                    src="/img/customer-clubs/newe.png"
                                    className="h-20"
                                />
                            </a>

                            <a
                                href="https://www.darlingtonasc.co.uk/"
                                target="_blank"
                                rel="noreferrer"
                                className="justify-self-center"
                            >
                                <img
                                    src="/img/customer-clubs/dare.png"
                                    className="h-20"
                                />
                            </a>

                            <a
                                href="https://nasc.co.uk/"
                                target="_blank"
                                rel="noreferrer"
                                className="justify-self-center"
                            >
                                <img
                                    src="/img/customer-clubs/nore.png"
                                    className="h-20"
                                />
                            </a>

                            <a
                                href="https://www.chesterlestreetasc.co.uk/"
                                target="_blank"
                                rel="noreferrer"
                                className="justify-self-center"
                            >
                                <img
                                    src="/img/chesterLogo.svg"
                                    className="h-20"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </Container>
    );
};

Index.layout = (page) => (
    <CentralMainLayout
    // title="My Account"
    // subtitle="Manage your personal details"
    >
        {page}
    </CentralMainLayout>
);

export default Index;
