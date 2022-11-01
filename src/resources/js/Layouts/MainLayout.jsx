/*
  This example requires Tailwind CSS v2.0+

  This example requires some changes to your config:

  ```
  // tailwind.config.js
  module.exports = {
    // ...
    plugins: [
      // ...
      require('@tailwindcss/forms'),
    ],
  }
  ```
*/
import { Fragment } from "react";
import { Menu, Popover, Transition } from "@headlessui/react";
import {
    ArrowLongLeftIcon,
    ChevronDownIcon,
    HomeIcon,
} from "@heroicons/react/24/solid";
import { Bars3Icon, XMarkIcon } from "@heroicons/react/24/outline";
import Footer from "./Components/Footer";
import ApplicationLogo from "@/Components/ApplicationLogo";
import { Head, usePage } from "@inertiajs/inertia-react";
import Link from "@/Components/BaseLink";
import MainHeader from "./Components/MainHeader";
import Container from "@/Components/Container";
import TenantLogo from "@/Components/TenantLogo";

const breadcrumbs = [
    { name: "Jobs", href: "#", current: false },
    { name: "Front End Developer", href: "#", current: false },
    { name: "Applicants", href: "#", current: true },
];
const userNavigation = [
    { name: "My Account", href: route("my_account.index") },
    { name: "Help", href: "https://docs.myswimmingclub.uk/", external: true },
    { name: "Sign out", href: route("logout"), method: "post" },
];

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

const MainLayout = ({ title, subtitle, children }) => {
    const userObject = usePage().props.auth.user;

    const navigation = usePage().props.tenant.menu;

    const user = userObject
        ? {
              name: `${userObject.Forename} ${userObject.Surname}`,
              email: userObject.EmailAddress,
              imageUrl: userObject.gravitar_url,
          }
        : null;

    return (
        <>
            {/*
        This example requires updating your template:

        ```
        <html class="h-full bg-gray-100">
        <body class="h-full">
        ```
      */}
            {/* Fall back to the layout title if no page title is set */}
            <Head title={title} />
            <div className="min-h-full bg-gray-100">
                <header className="bg-white shadow">
                    <Container>
                        <Popover className="flex justify-between h-16">
                            <div className="flex px-0 lg:px-0">
                                <div className="flex-shrink-0 flex items-center">
                                    <Link href="/">
                                        <TenantLogo className="h-8 w-auto" />
                                    </Link>
                                </div>
                                <nav
                                    aria-label="Global"
                                    className="hidden lg:ml-6 lg:flex lg:items-center lg:space-x-4"
                                >
                                    {navigation.map((item) => {
                                        if (item.external) {
                                            return (
                                                <a
                                                    key={item.name}
                                                    href={item.href}
                                                    className="px-3 py-2 text-gray-900 text-sm font-medium"
                                                >
                                                    {item.name}
                                                </a>
                                            );
                                        }
                                        if (item.children?.length > 0) {
                                            return (
                                                // <Link
                                                //     key={item.name}
                                                //     href={item.href}
                                                //     className="px-3 py-2 text-gray-900 text-sm font-medium"
                                                // >
                                                //     {item.name}
                                                // </Link>

                                                <Menu
                                                    as="div"
                                                    className="ml-1 relative flex-shrink-0"
                                                >
                                                    <div>
                                                        <Menu.Button className="p-2 text-gray-900 text-sm font-medium bg-white rounded flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <span className="sr-only">
                                                                Open {item.name}{" "}
                                                                menu
                                                            </span>
                                                            {item.name}{" "}
                                                            <ChevronDownIcon
                                                                className="flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-600"
                                                                aria-hidden="true"
                                                            />
                                                        </Menu.Button>
                                                    </div>
                                                    <Transition
                                                        as={Fragment}
                                                        enter="transition ease-out duration-100"
                                                        enterFrom="transform opacity-0 scale-95"
                                                        enterTo="transform opacity-100 scale-100"
                                                        leave="transition ease-in duration-75"
                                                        leaveFrom="transform opacity-100 scale-100"
                                                        leaveTo="transform opacity-0 scale-95"
                                                    >
                                                        <Menu.Items className="origin-top-left absolute -left-2 mt-2 max-w-56 whitespace-nowrap rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                                            {item.children.map(
                                                                (item) => (
                                                                    <Menu.Item
                                                                        key={
                                                                            item.name
                                                                        }
                                                                    >
                                                                        {({
                                                                            active,
                                                                        }) => (
                                                                            <Link
                                                                                key={
                                                                                    item.href
                                                                                }
                                                                                href={
                                                                                    item.href
                                                                                }
                                                                                className={classNames(
                                                                                    active
                                                                                        ? "bg-gray-100"
                                                                                        : "",
                                                                                    "block px-4 py-2 text-sm text-gray-700"
                                                                                )}
                                                                                method={
                                                                                    item.method
                                                                                }
                                                                            >
                                                                                {
                                                                                    item.name
                                                                                }
                                                                            </Link>
                                                                        )}
                                                                    </Menu.Item>
                                                                )
                                                            )}
                                                        </Menu.Items>
                                                    </Transition>
                                                </Menu>
                                            );
                                        }
                                        return (
                                            <Link
                                                key={item.name}
                                                href={item.href}
                                                className="px-3 py-2 text-gray-900 text-sm font-medium"
                                            >
                                                {item.name}
                                            </Link>
                                        );
                                    })}
                                </nav>
                            </div>
                            {/* <div className="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-end">
                                <div className="max-w-lg w-full lg:max-w-xs">
                                    <label htmlFor="search" className="sr-only">
                                        Search
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <SearchIcon
                                                className="h-5 w-5 text-gray-400"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <input
                                            id="search"
                                            name="search"
                                            className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white shadow-sm placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm"
                                            placeholder="Search"
                                            type="search"
                                        />
                                    </div>
                                </div>
                            </div> */}
                            <div className="flex items-center lg:hidden">
                                {/* Mobile menu button */}
                                <Popover.Button className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                    <span className="sr-only">
                                        Open main menu
                                    </span>
                                    <Bars3Icon
                                        className="block h-6 w-6"
                                        aria-hidden="true"
                                    />
                                </Popover.Button>
                            </div>
                            <Transition.Root as={Fragment}>
                                <div className="lg:hidden">
                                    <Transition.Child
                                        as={Fragment}
                                        enter="duration-150 ease-out"
                                        enterFrom="opacity-0"
                                        enterTo="opacity-100"
                                        leave="duration-150 ease-in"
                                        leaveFrom="opacity-100"
                                        leaveTo="opacity-0"
                                    >
                                        <Popover.Overlay
                                            className="z-20 fixed inset-0 bg-black bg-opacity-25"
                                            aria-hidden="true"
                                        />
                                    </Transition.Child>

                                    <Transition.Child
                                        as={Fragment}
                                        enter="duration-150 ease-out"
                                        enterFrom="opacity-0 scale-95"
                                        enterTo="opacity-100 scale-100"
                                        leave="duration-150 ease-in"
                                        leaveFrom="opacity-100 scale-100"
                                        leaveTo="opacity-0 scale-95"
                                    >
                                        <Popover.Panel
                                            focus
                                            className="z-30 absolute top-0 right-0 max-w-none w-full p-2 transition transform origin-top"
                                        >
                                            <div className="rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 bg-white divide-y divide-gray-200">
                                                <div className="pt-3 pb-2">
                                                    <div className="flex items-center justify-between px-4">
                                                        <div>
                                                            <ApplicationLogo className="h-8 w-auto" />
                                                        </div>
                                                        <div className="-mr-2">
                                                            <Popover.Button className="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                                                <span className="sr-only">
                                                                    Close menu
                                                                </span>
                                                                <XMarkIcon
                                                                    className="h-6 w-6"
                                                                    aria-hidden="true"
                                                                />
                                                            </Popover.Button>
                                                        </div>
                                                    </div>
                                                    <div className="mt-3 px-2 space-y-1">
                                                        {navigation.map(
                                                            (item) => {
                                                                if (
                                                                    item
                                                                        .children
                                                                        ?.length >
                                                                    0
                                                                ) {
                                                                    return (
                                                                        // <Link
                                                                        //     key={item.name}
                                                                        //     href={item.href}
                                                                        //     className="px-3 py-2 text-gray-900 text-sm font-medium"
                                                                        // >
                                                                        //     {item.name}
                                                                        // </Link>

                                                                        <Menu
                                                                            key={
                                                                                item.name
                                                                            }
                                                                        >
                                                                            <Menu.Button className="flex rounded-md px-3 py-2 text-base text-gray-900 font-medium hover:bg-gray-100 hover:text-gray-800">
                                                                                <span className="sr-only">
                                                                                    Open{" "}
                                                                                    {
                                                                                        item.name
                                                                                    }{" "}
                                                                                    menu
                                                                                </span>
                                                                                {
                                                                                    item.name
                                                                                }{" "}
                                                                                <ChevronDownIcon
                                                                                    className="flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-600"
                                                                                    aria-hidden="true"
                                                                                />
                                                                            </Menu.Button>
                                                                            <Transition
                                                                                as={
                                                                                    Fragment
                                                                                }
                                                                                enter="transition ease-out duration-100"
                                                                                enterFrom="transform opacity-0 scale-95"
                                                                                enterTo="transform opacity-100 scale-100"
                                                                                leave="transition ease-in duration-75"
                                                                                leaveFrom="transform opacity-100 scale-100"
                                                                                leaveTo="transform opacity-0 scale-95"
                                                                            >
                                                                                <Menu.Items className="mt-2 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                                                                    {item.children.map(
                                                                                        (
                                                                                            item
                                                                                        ) => (
                                                                                            <Menu.Item
                                                                                                key={
                                                                                                    item.name
                                                                                                }
                                                                                            >
                                                                                                {({
                                                                                                    active,
                                                                                                }) => (
                                                                                                    <Popover.Button
                                                                                                        as={
                                                                                                            Link
                                                                                                        }
                                                                                                        href={
                                                                                                            item.href
                                                                                                        }
                                                                                                        className={classNames(
                                                                                                            active
                                                                                                                ? "bg-gray-100"
                                                                                                                : "",
                                                                                                            "block px-4 py-2 text-gray-700"
                                                                                                        )}
                                                                                                        method={
                                                                                                            item.method
                                                                                                        }
                                                                                                    >
                                                                                                        {
                                                                                                            item.name
                                                                                                        }
                                                                                                    </Popover.Button>
                                                                                                )}
                                                                                            </Menu.Item>
                                                                                        )
                                                                                    )}
                                                                                </Menu.Items>
                                                                            </Transition>
                                                                        </Menu>
                                                                    );
                                                                }
                                                                return (
                                                                    <Popover.Button
                                                                        as={
                                                                            Link
                                                                        }
                                                                        key={
                                                                            item.name
                                                                        }
                                                                        href={
                                                                            item.href
                                                                        }
                                                                        className="block rounded-md px-3 py-2 text-base text-gray-900 font-medium hover:bg-gray-100 hover:text-gray-800"
                                                                    >
                                                                        {
                                                                            item.name
                                                                        }
                                                                    </Popover.Button>
                                                                );
                                                            }
                                                        )}
                                                    </div>
                                                </div>
                                                {user && (
                                                    <div className="pt-4 pb-2">
                                                        <div className="flex items-center px-5">
                                                            <div className="flex-shrink-0">
                                                                <img
                                                                    className="h-10 w-10 rounded-full"
                                                                    src={
                                                                        user.imageUrl
                                                                    }
                                                                    alt=""
                                                                />
                                                            </div>
                                                            <div className="ml-3">
                                                                <div className="text-base font-medium text-gray-800">
                                                                    {user.name}
                                                                </div>
                                                                <div className="text-sm font-medium text-gray-500">
                                                                    {user.email}
                                                                </div>
                                                            </div>
                                                            {/* We don't have notifications yet */}
                                                            {/* <button
                                                            type="button"
                                                            className="ml-auto flex-shrink-0 bg-white p-1 text-gray-400 rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                        >
                                                            <span className="sr-only">
                                                                View
                                                                notifications
                                                            </span>
                                                            <BellIcon
                                                                className="h-6 w-6"
                                                                aria-hidden="true"
                                                            />
                                                        </button> */}
                                                        </div>
                                                        <div className="mt-3 px-2 space-y-1">
                                                            {userNavigation.map(
                                                                (item, idx) => (
                                                                    <Popover.Button
                                                                        as={
                                                                            Link
                                                                        }
                                                                        key={
                                                                            idx
                                                                        }
                                                                        href={
                                                                            item.href
                                                                        }
                                                                        className="block rounded-md px-3 py-2 text-base text-gray-900 font-medium hover:bg-gray-100 hover:text-gray-800"
                                                                        method={
                                                                            item.method
                                                                        }
                                                                        external={
                                                                            item.external
                                                                        }
                                                                    >
                                                                        {
                                                                            item.name
                                                                        }
                                                                    </Popover.Button>
                                                                )
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </Popover.Panel>
                                    </Transition.Child>
                                </div>
                            </Transition.Root>
                            <div className="hidden lg:ml-4 lg:flex lg:items-center">
                                {/* We don't have notifications yet */}
                                {/* <button
                                    type="button"
                                    className="flex-shrink-0 bg-white p-1 text-gray-400 rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span className="sr-only">
                                        View notifications
                                    </span>
                                    <BellIcon
                                        className="h-6 w-6"
                                        aria-hidden="true"
                                    />
                                </button> */}

                                {/* Profile dropdown */}
                                {user && (
                                    <Menu
                                        as="div"
                                        className="ml-4 relative flex-shrink-0"
                                    >
                                        <div>
                                            <Menu.Button className="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <span className="sr-only">
                                                    Open user menu
                                                </span>
                                                <img
                                                    className="h-8 w-8 rounded-full"
                                                    src={user.imageUrl}
                                                    alt=""
                                                />
                                            </Menu.Button>
                                        </div>
                                        <Transition
                                            as={Fragment}
                                            enter="transition ease-out duration-100"
                                            enterFrom="transform opacity-0 scale-95"
                                            enterTo="transform opacity-100 scale-100"
                                            leave="transition ease-in duration-75"
                                            leaveFrom="transform opacity-100 scale-100"
                                            leaveTo="transform opacity-0 scale-95"
                                        >
                                            <Menu.Items className="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                                                {userNavigation.map(
                                                    (item, idx) => (
                                                        <Menu.Item key={idx}>
                                                            {({ active }) => (
                                                                <Link
                                                                    href={
                                                                        item.href
                                                                    }
                                                                    className={classNames(
                                                                        active
                                                                            ? "bg-gray-100"
                                                                            : "",
                                                                        "block px-4 py-2 text-sm text-gray-700"
                                                                    )}
                                                                    method={
                                                                        item.method
                                                                    }
                                                                    external={
                                                                        item.external
                                                                    }
                                                                >
                                                                    {item.name}
                                                                </Link>
                                                            )}
                                                        </Menu.Item>
                                                    )
                                                )}
                                            </Menu.Items>
                                        </Transition>
                                    </Menu>
                                )}
                            </div>
                        </Popover>
                    </Container>

                    {false && (
                        <div className="max-w-7xl mx-auto px-4 sm:px-6">
                            <div className="border-t border-gray-200 py-3">
                                <nav className="flex" aria-label="Breadcrumb">
                                    <div className="flex sm:hidden">
                                        <Link
                                            href="#"
                                            className="group inline-flex space-x-3 text-sm font-medium text-gray-500 hover:text-gray-700"
                                        >
                                            <ArrowLongLeftIcon
                                                className="flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-600"
                                                aria-hidden="true"
                                            />
                                            <span>Back to Applicants</span>
                                        </Link>
                                    </div>
                                    <div className="hidden sm:block">
                                        <ol
                                            role="list"
                                            className="flex items-center space-x-4"
                                        >
                                            <li>
                                                <div>
                                                    <Link
                                                        href="#"
                                                        className="text-gray-400 hover:text-gray-500"
                                                    >
                                                        <HomeIcon
                                                            className="flex-shrink-0 h-5 w-5"
                                                            aria-hidden="true"
                                                        />
                                                        <span className="sr-only">
                                                            Home
                                                        </span>
                                                    </Link>
                                                </div>
                                            </li>
                                            {breadcrumbs.map((item) => (
                                                <li key={item.name}>
                                                    <div className="flex items-center">
                                                        <svg
                                                            className="flex-shrink-0 h-5 w-5 text-gray-300"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="currentColor"
                                                            viewBox="0 0 20 20"
                                                            aria-hidden="true"
                                                        >
                                                            <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                                        </svg>
                                                        <Link
                                                            href={item.href}
                                                            className="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                                                            aria-current={
                                                                item.current
                                                                    ? "page"
                                                                    : undefined
                                                            }
                                                        >
                                                            {item.name}
                                                        </Link>
                                                    </div>
                                                </li>
                                            ))}
                                        </ol>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    )}
                </header>

                <main className="py-10 min-h-screen">
                    {/* Page header */}
                    <MainHeader title={title} subtitle={subtitle}></MainHeader>

                    {children}
                </main>
            </div>

            <Footer />
        </>
    );
};

export default MainLayout;
