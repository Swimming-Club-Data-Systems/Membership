import React from "react";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Link from "@/Components/Link";

const About = (props) => {
    return (
        <>
            <Head
                title={`Welcome to the ${props.tenant.name} Membership System`}
            />

            <Container>
                <div className="prose prose-sm">
                    <h2>General Information</h2>
                    <p>
                        Welcome to SCDS Next - The SCDS Membership System
                        evolved for the future. The membership system now
                        consists of our modern, Laravel and React based
                        application and our older statically rendered
                        application.
                    </p>

                    <p>
                        Over time, we will migrate features to our new Laravel
                        and React based application. The first features to move
                        have been <em>Authentication</em> and{" "}
                        <em>My Account</em>.
                    </p>

                    <p>
                        This page gives information about the application which
                        you may need to give to Test Club or SCDS if you have an
                        issue.
                    </p>

                    <h2>Getting Help</h2>
                    <p>
                        Check out our{" "}
                        <a href="https://docs.myswimmingclub.uk">
                            help and support documentation
                        </a>{" "}
                        in the first instance. It contains a wealth of
                        information about widely used and specialised parts of
                        the membership system.
                    </p>

                    <p>
                        If you can&apos;t find the help you need, you should
                        then try contacting your club. See{" "}
                        <a href={props.tenant.website}>your club website</a> for
                        contact details. Your club can reach out to SCDS for
                        further support if required.
                    </p>

                    <h2>Support Information</h2>

                    <p>
                        If we (SCDS) have asked for your tenant details so that
                        we can solve a problem, please send us the following:
                    </p>

                    <dl className="grid grid-cols-1 gap-y-4 sm:grid-cols-1">
                        <div className="sm:col-span-1">
                            <dt className="text-sm font-medium text-gray-500">
                                Tenant
                            </dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {props.tenant.name}
                            </dd>
                        </div>
                        <div className="sm:col-span-1">
                            <dt className="text-sm font-medium text-gray-500">
                                Tenant code
                            </dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {props.tenant.asa_code}
                            </dd>
                        </div>
                        <div className="sm:col-span-1">
                            <dt className="text-sm font-medium text-gray-500">
                                User ID
                            </dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {props?.auth?.user && (
                                    <>{props.auth.user.UserID}</>
                                )}
                                {!props?.auth?.user && <>N/A - Not logged in</>}
                            </dd>
                        </div>
                        <div className="sm:col-span-1">
                            <dt className="text-sm font-medium text-gray-500">
                                User Agent String
                            </dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {navigator.userAgent}
                            </dd>
                        </div>
                    </dl>

                    <h2>Legal</h2>
                    <p>
                        This product includes GeoLite2 data created by MaxMind,
                        available from{" "}
                        <a href="https://www.maxmind.com">
                            https://www.maxmind.com
                        </a>
                        .
                    </p>
                </div>
            </Container>
        </>
    );
};

About.layout = (page) => (
    <MainLayout
        title="About this system"
        subtitle="This software is written by Swimming Club Data Systems."
    >
        {page}
    </MainLayout>
);

export default About;
