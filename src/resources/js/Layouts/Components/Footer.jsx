import { Link, usePage } from "@inertiajs/inertia-react";
import React from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import InternalContainer from "@/Components/InternalContainer";

const Footer = (props) => {
    const { tenant } = usePage().props;

    return (
        <div className="bg-gray-200 text-gray-700  dark:bg-slate-900 dark:text-slate-200">
            <InternalContainer>
                <div className="grid grid-cols-1 gap-16 border-b border-gray-300 py-5 dark:border-slate-200 md:grid-cols-3 lg:grid-cols-5">
                    <div className="md:col-span-3 lg:col-span-2">
                        <ApplicationLogo />

                        <p className="pt-5">
                            Helping swimming clubs across the UK run
                            efficiently.
                        </p>
                    </div>
                    <div className="space-y-4">
                        <p className="font-semibold text-gray-600 dark:text-slate-300">
                            Help and Support
                        </p>
                        <div>
                            <a href="https://docs.myswimmingclub.uk/">
                                Documentation
                            </a>
                        </div>

                        <div>
                            <a href="https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u">
                                Report mail abuse
                            </a>
                        </div>

                        {/*<div>*/}
                        {/*    <a href="https://docs.myswimmingclub.uk/">*/}
                        {/*        What&apos;s new?*/}
                        {/*    </a>*/}
                        {/*</div>*/}
                    </div>

                    {!tenant && (
                        <div className="space-y-4">
                            <p className="font-semibold text-gray-600 dark:text-slate-300">
                                Organisation
                            </p>
                            <div>
                                <Link href={route("central.login")}>
                                    Admin Login
                                </Link>
                            </div>

                            <div>
                                <Link>About Us</Link>
                            </div>

                            <div>
                                <a href="https://climate.stripe.com/pkIT9H">
                                    Carbon Removal
                                </a>
                            </div>

                            <div>
                                <a
                                    href="https://github.com/Swimming-Club-Data-Systems"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    GitHub
                                </a>
                            </div>
                        </div>
                    )}

                    {tenant && (
                        <div className="space-y-4">
                            <p className="font-semibold text-gray-600 dark:text-slate-300">
                                {tenant.name}
                            </p>

                            <div>
                                <Link href="/privacy">Privacy Policy</Link>
                            </div>

                            <div>
                                <a href={tenant.website}>Club Website</a>
                            </div>
                        </div>
                    )}

                    <div className="space-y-4">
                        <p className="font-semibold text-gray-600 dark:text-slate-300">
                            Related Sites
                        </p>
                        <div>
                            <a
                                href="https://www.britishswimming.org/"
                                target="_blank"
                                rel="noreferrer"
                            >
                                British Swimming
                            </a>
                        </div>

                        <div>
                            <a
                                href="https://www.swimming.org/swimengland/"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Swim England
                            </a>
                        </div>

                        <div>
                            <a
                                href="https://www.swimming.org/swimengland/"
                                target="_blank"
                                rel="noreferrer"
                            >
                                swimming.org
                            </a>
                        </div>
                    </div>
                </div>
                <p className="py-5 text-center text-sm font-semibold text-gray-500 dark:text-slate-400">
                    &copy; Swimming Club Data Systems 2022
                </p>
            </InternalContainer>
        </div>
    );
};

export default Footer;
