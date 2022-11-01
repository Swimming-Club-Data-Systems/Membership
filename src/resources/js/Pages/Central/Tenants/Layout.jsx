import React from "react";
import {
    ChartPieIcon,
    CogIcon,
    CurrencyPoundIcon,
    KeyIcon,
    BuildingOffice2Icon,
    UserCircleIcon,
    CreditCardIcon,
    UsersIcon,
    BanknotesIcon,
} from "@heroicons/react/24/outline";
import Container from "@/Components/Container";
import { Link, usePage } from "@inertiajs/inertia-react";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

const Layout = (props) => {
    const tenantId = usePage().props.id;

    const navigation = [
        {
            name: "Details",
            href: route("central.tenants.show", tenantId),
            icon: BuildingOffice2Icon,
            current: route().current("central.tenants.show", tenantId),
        },
        {
            name: "Billing",
            href: route("central.tenants.billing", tenantId),
            icon: CreditCardIcon,
            current: route().current("central.tenants.billing", tenantId),
        },
        {
            name: "Pay As You Go",
            href: route("central.tenants.pay_as_you_go", tenantId),
            icon: BanknotesIcon,
            current: route().current("central.tenants.pay_as_you_go", tenantId),
        },
        {
            name: "Stripe Account",
            href: route("central.tenants.stripe", tenantId),
            icon: CurrencyPoundIcon,
            current: route().current("central.tenants.stripe", tenantId),
        },
        // {
        //     name: "Statistics",
        //     href: route("central.tenants.statistics", tenantId),
        //     icon: ChartPieIcon,
        //     current: route().current("central.tenants.statistics", tenantId),
        // },
        // {
        //     name: "Config Keys",
        //     href: route("central.tenants.config_keys", tenantId),
        //     icon: CogIcon,
        //     current: route().current("central.tenants.config_keys", tenantId),
        // },
        {
            name: "Administrators",
            href: route("central.tenants.administrators", tenantId),
            icon: UsersIcon,
            current: route().current(
                "central.tenants.administrators",
                tenantId
            ),
        },
        // {
        //     name: "Advanced Options",
        //     href: route("my_account.advanced"),
        //     icon: AdjustmentsIcon,
        //     current: route().current("my_account.advanced"),
        // },
    ];

    return (
        <Container noMargin className="my-12">
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
