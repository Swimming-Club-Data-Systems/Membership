import React from "react";
import { HomeIcon } from "@heroicons/react/24/solid";
import { Link, usePage } from "@inertiajs/react";

interface Props {
    crumbs?: {
        name: string;
        href: string;
    }[];
}

const Breadcrumbs: React.FC<Props> = (props) => {
    const { url } = usePage();

    if (props.crumbs) {
        return (
            <nav
                className="flex border-b border-gray-200 bg-white"
                aria-label="Breadcrumb"
            >
                <ol className="mx-auto flex w-full space-x-4 px-4 sm:px-6 lg:px-8">
                    <li className="flex">
                        <div className="flex items-center">
                            <Link
                                href="/"
                                className="text-gray-400 hover:text-gray-500"
                            >
                                <HomeIcon
                                    className="h-5 w-5 flex-shrink-0"
                                    aria-hidden="true"
                                />
                                <span className="sr-only">Home</span>
                            </Link>
                        </div>
                    </li>
                    {props.crumbs.map((page) => (
                        <li key={page.name} className="flex">
                            <div className="flex items-center">
                                <svg
                                    className="h-full w-6 flex-shrink-0 text-gray-200"
                                    viewBox="0 0 24 44"
                                    preserveAspectRatio="none"
                                    fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true"
                                >
                                    <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
                                </svg>
                                <Link
                                    href={page.href}
                                    className="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                                    aria-current={
                                        page.href === url ||
                                        page.href === window.location.href
                                            ? "page"
                                            : undefined
                                    }
                                >
                                    {page.name}
                                </Link>
                            </div>
                        </li>
                    ))}
                </ol>
            </nav>
        );
    }
    return null;
};

export default Breadcrumbs;
