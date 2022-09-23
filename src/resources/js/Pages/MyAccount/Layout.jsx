import React from "react";
import {
    AdjustmentsIcon,
    KeyIcon,
    MailIcon,
    UserCircleIcon,
} from "@heroicons/react/outline";
import Container from "@/Components/Container";
import { Link } from "@inertiajs/inertia-react";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

const Layout = (props) => {
    const navigation = [
        {
            name: "Profile",
            href: route("my_account.profile"),
            icon: UserCircleIcon,
            current: route().current("my_account.profile"),
        },
        {
            name: "Email Options",
            href: route("my_account.email"),
            icon: MailIcon,
            current: route().current("my_account.email"),
        },
        {
            name: "Password & Security",
            href: route("my_account.security"),
            icon: KeyIcon,
            current: route().current("my_account.security"),
        },
        // {
        //     name: "Advanced Options",
        //     href: route("my_account.advanced"),
        //     icon: AdjustmentsIcon,
        //     current: route().current("my_account.advanced"),
        // },
        // { name: "Integrations", href: "#", icon: ViewGridAddIcon, current: false },
    ];

    return (
        <Container noMargin className="mb-12">
            <div className="lg:grid lg:grid-cols-12 lg:gap-x-5">
                <aside className="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                    <nav className="space-y-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={classNames(
                                    item.current
                                        ? "bg-gray-50 text-indigo-700 hover:text-indigo-700 hover:bg-white"
                                        : "text-gray-900 hover:text-gray-900 hover:bg-gray-50",
                                    "group rounded-md px-3 py-2 flex items-center text-sm font-medium"
                                )}
                                aria-current={item.current ? "page" : undefined}
                                preserveScroll
                            >
                                <item.icon
                                    className={classNames(
                                        item.current
                                            ? "text-indigo-500 group-hover:text-indigo-500"
                                            : "text-gray-400 group-hover:text-gray-500",
                                        "flex-shrink-0 -ml-1 mr-3 h-6 w-6"
                                    )}
                                    aria-hidden="true"
                                />
                                <span className="truncate">{item.name}</span>
                            </Link>
                        ))}
                    </nav>
                </aside>

                {props.children}
            </div>
        </Container>
    );
};

export default Layout;
