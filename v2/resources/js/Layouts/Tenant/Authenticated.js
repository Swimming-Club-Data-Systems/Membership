import React, { Fragment, useState } from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Dropdown from "@/Components/Dropdown";
import NavLink from "@/Components/NavLink";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink";
import { usePage, Link } from "@inertiajs/inertia-react";
import Footer from "@/Components/Footer";
import Container from "@/Components/Container";
import PageHeader from "@/Components/PageHeader";
import { Dialog, Transition, Disclosure } from "@headlessui/react";
import {
  CalendarIcon,
  ChartBarIcon,
  FolderIcon,
  HomeIcon,
  InboxIcon,
  MenuIcon,
  UsersIcon,
  XIcon,
  UserGroupIcon,
  ClipboardListIcon,
  CurrencyPoundIcon,
  MailIcon,
  CogIcon,
} from "@heroicons/react/outline";
import Breadcrumbs from "@/Components/Breadcrumbs";

// const navigation = [
//   { name: "Dashboard", href: "#", icon: HomeIcon, current: true },
//   { name: "Team", href: "#", icon: UsersIcon, current: false },
//   { name: "Projects", href: "#", icon: FolderIcon, current: false },
//   { name: "Calendar", href: "#", icon: CalendarIcon, current: false },
//   { name: "Documents", href: "#", icon: InboxIcon, current: false },
//   { name: "Reports", href: "#", icon: ChartBarIcon, current: false },
// ];

const navigation = [
  { name: "Dashboard", href: "/", icon: HomeIcon },
  { name: "Members", href: "/members", icon: UsersIcon },
  { name: "Squads", href: "/squads", icon: UserGroupIcon },
  { name: "Attendance", href: "/attendance", icon: ClipboardListIcon },
  {
    name: "Users",
    href: "/users",
    icon: UsersIcon,
    children: [
      { name: "List", href: "/users" },
      { name: "Add new", href: "/users/new" },
    ],
  },
  { name: "Payments", href: "/payments", icon: CurrencyPoundIcon },
  { name: "Notify", href: "/notify", icon: MailIcon },
  { name: "Galas", href: "/galas", icon: HomeIcon },
  { name: "Admin", href: "/admin", icon: CogIcon },
];

function classNames(...classes) {
  return classes.filter(Boolean).join(" ");
}

const Layout = (props) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { url } = usePage();
  const { auth } = usePage().props;

  const navContents = () => {
    return navigation.map((item) => {
      const current =
        (url.startsWith(item.href) && item.href !== "/") || url === item.href;
      return !item.children ? (
        <Link
          key={item.name}
          href={item.href}
          className={classNames(
            current
              ? "bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white"
              : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white",
            "group flex items-center rounded-md px-2 py-2 text-base font-medium"
          )}
        >
          <item.icon
            className={classNames(
              current
                ? "text-grey-500 dark:text-gray-300"
                : "text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300",
              "mr-4 h-6 w-6 flex-shrink-0"
            )}
            aria-hidden="true"
          />
          {item.name}
        </Link>
      ) : (
        <Disclosure
          as="div"
          key={item.name}
          className="space-y-1"
          // defaultOpen={current}
        >
          {({ open }) => (
            <>
              <Disclosure.Button
                className={classNames(
                  current
                    ? "bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white"
                    : "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white",
                  "group flex w-full items-center rounded-md py-2 px-2 text-left text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
                )}
              >
                <item.icon
                  className={classNames(
                    current
                      ? "text-grey-500 dark:text-gray-300"
                      : "text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300",
                    "mr-4 h-6 w-6 flex-shrink-0"
                  )}
                  aria-hidden="true"
                />

                {item.name}
                <svg
                  className={classNames(
                    open
                      ? "text-grey-500 rotate-90 dark:text-gray-300"
                      : "text-gray-400",
                    "ml-auto h-5 w-5 flex-shrink-0 transform transition-colors duration-150 ease-in-out group-hover:text-gray-400"
                  )}
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                >
                  <path d="M6 6L14 10L6 14V6Z" fill="currentColor" />
                </svg>
              </Disclosure.Button>
              <Disclosure.Panel className="space-y-1">
                {item.children.map((subItem) => (
                  <Disclosure.Button
                    key={subItem.name}
                    as={Link}
                    href={subItem.href}
                    className={classNames(
                      "text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white",
                      "group flex w-full items-center rounded-md py-2 pl-12 pr-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    )}
                  >
                    {subItem.name}
                  </Disclosure.Button>
                ))}
              </Disclosure.Panel>
            </>
          )}
        </Disclosure>
      );
    });
  };

  return (
    <>
      {/*
        This example requires updating your template:

        ```
        <html class="h-full bg-gray-100">
        <body class="h-full">
        ```
      */}
      <div>
        <Transition.Root show={sidebarOpen} as={Fragment}>
          <Dialog
            as="div"
            className="fixed inset-0 z-40 flex lg:hidden"
            onClose={setSidebarOpen}
          >
            <Transition.Child
              as={Fragment}
              enter="transition-opacity ease-linear duration-300"
              enterFrom="opacity-0"
              enterTo="opacity-100"
              leave="transition-opacity ease-linear duration-300"
              leaveFrom="opacity-100"
              leaveTo="opacity-0"
            >
              <Dialog.Overlay className="fixed inset-0 bg-gray-600 bg-opacity-75" />
            </Transition.Child>
            <Transition.Child
              as={Fragment}
              enter="transition ease-in-out duration-300 transform"
              enterFrom="-translate-x-full"
              enterTo="translate-x-0"
              leave="transition ease-in-out duration-300 transform"
              leaveFrom="translate-x-0"
              leaveTo="-translate-x-full"
            >
              <div className="relative flex w-full max-w-xs flex-1 flex-col bg-white dark:bg-gray-800">
                <Transition.Child
                  as={Fragment}
                  enter="ease-in-out duration-300"
                  enterFrom="opacity-0"
                  enterTo="opacity-100"
                  leave="ease-in-out duration-300"
                  leaveFrom="opacity-100"
                  leaveTo="opacity-0"
                >
                  <div className="absolute top-0 right-0 -mr-12 pt-2">
                    <button
                      type="button"
                      className="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                      onClick={() => setSidebarOpen(false)}
                    >
                      <span className="sr-only">Close sidebar</span>
                      <XIcon
                        className="h-6 w-6 text-white"
                        aria-hidden="true"
                      />
                    </button>
                  </div>
                </Transition.Child>
                <div className="h-0 flex-1 overflow-y-auto pt-5 pb-4">
                  <div className="flex flex-shrink-0 items-center px-4">
                    <ApplicationLogo />
                  </div>
                  <nav className="mt-5 space-y-1 px-2">{navContents()}</nav>
                </div>
                <div className="flex flex-shrink-0 bg-gray-700 p-4">
                  <Link
                    href={route("myaccount.index")}
                    className="group block flex-shrink-0"
                  >
                    <div className="flex items-center">
                      <div>
                        <img
                          className="inline-block h-10 w-10 rounded-full"
                          src={auth.user.gravitar_url}
                          alt=""
                        />
                      </div>
                      <div className="ml-3">
                        <p className="text-base font-medium text-white">
                          {`${auth.user.first_name} ${auth.user.last_name}`}
                        </p>
                        <p className="text-sm font-medium text-gray-400 group-hover:text-gray-300">
                          View profile
                        </p>
                      </div>
                    </div>
                  </Link>
                </div>
              </div>
            </Transition.Child>
            <div className="w-14 flex-shrink-0">
              {/* Force sidebar to shrink to fit close icon */}
            </div>
          </Dialog>
        </Transition.Root>

        {/* Static sidebar for desktop */}
        <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
          {/* Sidebar component, swap this element with another sidebar if you like */}
          <div className="flex min-h-0 flex-1 flex-col border-r border-gray-200 bg-gray-100 dark:bg-gray-800">
            <div className="flex flex-1 flex-col overflow-y-auto pt-5 pb-4">
              <div className="flex flex-shrink-0 items-center px-4">
                <ApplicationLogo />
              </div>
              <nav className="mt-5 flex-1 space-y-1 px-2">{navContents()}</nav>
            </div>
            <div className="flex flex-shrink-0 bg-gray-200 p-4 dark:bg-gray-700">
              <Link
                href={route("myaccount.index")}
                className="group block w-full flex-shrink-0"
              >
                <div className="flex items-center">
                  <div>
                    <img
                      className="inline-block h-9 w-9 rounded-full"
                      src={auth.user.gravitar_url}
                      alt=""
                    />
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-900 dark:text-white">{`${auth.user.first_name} ${auth.user.last_name}`}</p>
                    <p className="text-grey-700 text-xs font-medium group-hover:text-gray-800 dark:text-gray-300 dark:group-hover:text-gray-200">
                      View profile
                    </p>
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>
        <div className="flex flex-1 flex-col lg:pl-64">
          <div className="sticky top-0 z-10 bg-gray-100 pl-1 pt-1 sm:pl-3 sm:pt-3 lg:hidden">
            <button
              type="button"
              className="-ml-0.5 -mt-0.5 inline-flex h-12 w-12 items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              onClick={() => setSidebarOpen(true)}
            >
              <span className="sr-only">Open sidebar</span>
              <MenuIcon className="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
          {/* HEADER START */}
          <Breadcrumbs crumbs={props.crumbs} />
          <div className="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
            <div className="md:flex md:items-center md:justify-between">
              <div className="min-w-0 flex-1">
                <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl">
                  {props.title}
                </h2>
              </div>
              <div className="mt-4 flex md:mt-0 md:ml-4">
                <button
                  type="button"
                  className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                  Edit
                </button>
                <button
                  type="button"
                  className="ml-3 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                  Publish
                </button>
              </div>
            </div>
          </div>
          {/* HEADER END */}
          <main className="flex-1">
            <div className="py-6">
              <div className="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
                {props.children}
              </div>
            </div>
            <Footer />
          </main>
        </div>
      </div>
    </>
  );
};

const AuthenticatedOld = ({ children, ...otherProps }) => {
  const { auth, errors } = usePage().props;

  const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

  return (
    <>
      <PageHeader
        title={otherProps.title}
        subtitle={otherProps.subtitle}
        header={otherProps.header}
        crumbs={otherProps.crumbs}
      />
      <div className="min-h-screen bg-gray-100">
        <Container>
          <main>{children}</main>
        </Container>
      </div>
      <Footer />
    </>
  );
};

export default Layout;
