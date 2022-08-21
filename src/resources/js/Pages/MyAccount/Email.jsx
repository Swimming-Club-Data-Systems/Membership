import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Layout from "./Layout";

const Email = (props) => {
    return (
        <>
            <Head title="My Account" />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <form action="#" method="POST">
                    <div className="shadow sm:rounded-md sm:overflow-hidden">
                        <div className="bg-white py-6 px-4 space-y-6 sm:p-6">
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900">
                                    Notifications
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Provide basic informtion about the job. Be
                                    specific with the job title.
                                </p>
                            </div>

                            <fieldset>
                                <legend className="text-base font-medium text-gray-900">
                                    By Email
                                </legend>
                                <div className="mt-4 space-y-4">
                                    <div className="flex items-start">
                                        <div className="h-5 flex items-center">
                                            <input
                                                id="comments"
                                                name="comments"
                                                type="checkbox"
                                                className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            />
                                        </div>
                                        <div className="ml-3 text-sm">
                                            <label
                                                htmlFor="comments"
                                                className="font-medium text-gray-700"
                                            >
                                                Comments
                                            </label>
                                            <p className="text-gray-500">
                                                Get notified when someones posts
                                                a comment on a posting.
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="flex items-start">
                                            <div className="h-5 flex items-center">
                                                <input
                                                    id="candidates"
                                                    name="candidates"
                                                    type="checkbox"
                                                    className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                />
                                            </div>
                                            <div className="ml-3 text-sm">
                                                <label
                                                    htmlFor="candidates"
                                                    className="font-medium text-gray-700"
                                                >
                                                    Candidates
                                                </label>
                                                <p className="text-gray-500">
                                                    Get notified when a
                                                    candidate applies for a job.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="flex items-start">
                                            <div className="h-5 flex items-center">
                                                <input
                                                    id="offers"
                                                    name="offers"
                                                    type="checkbox"
                                                    className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                />
                                            </div>
                                            <div className="ml-3 text-sm">
                                                <label
                                                    htmlFor="offers"
                                                    className="font-medium text-gray-700"
                                                >
                                                    Offers
                                                </label>
                                                <p className="text-gray-500">
                                                    Get notified when a
                                                    candidate accepts or rejects
                                                    an offer.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset className="mt-6">
                                <legend className="text-base font-medium text-gray-900">
                                    Push Notifications
                                </legend>
                                <p className="text-sm text-gray-500">
                                    These are delivered via SMS to your mobile
                                    phone.
                                </p>
                                <div className="mt-4 space-y-4">
                                    <div className="flex items-center">
                                        <input
                                            id="push-everything"
                                            name="push-notifications"
                                            type="radio"
                                            className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        />
                                        <label
                                            htmlFor="push-everything"
                                            className="ml-3"
                                        >
                                            <span className="block text-sm font-medium text-gray-700">
                                                Everything
                                            </span>
                                        </label>
                                    </div>
                                    <div className="flex items-center">
                                        <input
                                            id="push-email"
                                            name="push-notifications"
                                            type="radio"
                                            className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        />
                                        <label
                                            htmlFor="push-email"
                                            className="ml-3"
                                        >
                                            <span className="block text-sm font-medium text-gray-700">
                                                Same as email
                                            </span>
                                        </label>
                                    </div>
                                    <div className="flex items-center">
                                        <input
                                            id="push-nothing"
                                            name="push-notifications"
                                            type="radio"
                                            className="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        />
                                        <label
                                            htmlFor="push-nothing"
                                            className="ml-3"
                                        >
                                            <span className="block text-sm font-medium text-gray-700">
                                                No push notifications
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button
                                type="submit"
                                className="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
};

Email.layout = (page) => (
    <MainLayout title="My Account" subtitle="Manage your personal details">
        <Layout children={page} />
    </MainLayout>
);

export default Email;
