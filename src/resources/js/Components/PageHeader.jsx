import { Link, usePage } from "@inertiajs/inertia-react";
import React from "react";
import ApplicationLogo from "./ApplicationLogo";
import Container from "./Container";
import Breadcrumbs from "./Breadcrumbs.jsx";

/* This example requires Tailwind CSS v2.0+ */
import { Fragment } from "react";
import { Disclosure, Menu, Transition } from "@headlessui/react";
import { BellIcon, MenuIcon, XIcon } from "@heroicons/react/outline";

const navigation = [
  { name: "Dashboard", href: "/" },
  { name: "Members", href: "/members" },
  { name: "Squads", href: "/squads" },
  { name: "Attendance", href: "/attendance" },
  { name: "Users", href: "/users" },
  { name: "Payments", href: "/payments" },
  { name: "Notify", href: "/notify" },
  { name: "Galas", href: "/galas" },
  { name: "Admin", href: "/admin" },
];
const userNavigation = [
  { name: "Your Profile", href: route("myaccount.index") },
  { name: "Settings", href: "#" },
  { name: "Sign out", href: route("logout") },
];

function classNames(...classes) {
  return classes.filter(Boolean).join(" ");
}

const PageHeader = (props) => {
  const { url } = usePage();
  const { auth } = usePage().props;

  const user = {
    name: `${auth.user.first_name} ${auth.user.last_name}`,
    email: `${auth.user.email}`,
    imageUrl: `${auth.user.gravitar_url}`,
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
      <div className="min-h-full">
        <Disclosure as="nav" className="bg-gray-800">
          {({ open }) => (
            <>
              <Container className="py-0">
                <div className="flex h-16 items-center justify-between">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <ApplicationLogo />
                    </div>
                    <div className="hidden md:block">
                      <div className="ml-10 flex items-baseline space-x-4">
                        {navigation.map((item) => {
                          const current =
                            (url.startsWith(item.href) && item.href !== "/") ||
                            url === item.href;
                          return (
                            <Link
                              key={item.name}
                              href={item.href}
                              className={classNames(
                                current
                                  ? "bg-gray-900 text-white"
                                  : "text-gray-300 hover:bg-gray-700 hover:text-white",
                                "rounded-md px-3 py-2 text-sm font-medium"
                              )}
                              aria-current={current ? "page" : undefined}
                            >
                              {item.name}
                            </Link>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                  <div className="hidden md:block">
                    <div className="ml-4 flex items-center md:ml-6">
                      <button
                        type="button"
                        className="rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                      >
                        <span className="sr-only">View notifications</span>
                        <BellIcon className="h-6 w-6" aria-hidden="true" />
                      </button>

                      {/* Profile dropdown */}
                      <Menu as="div" className="relative ml-3">
                        <div>
                          <Menu.Button className="flex max-w-xs items-center rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span className="sr-only">Open user menu</span>
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
                          <Menu.Items className="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            {userNavigation.map((item) => (
                              <Menu.Item key={item.name}>
                                {({ active }) => (
                                  <a
                                    href={item.href}
                                    className={classNames(
                                      active ? "bg-gray-100" : "",
                                      "block px-4 py-2 text-sm text-gray-700"
                                    )}
                                  >
                                    {item.name}
                                  </a>
                                )}
                              </Menu.Item>
                            ))}
                          </Menu.Items>
                        </Transition>
                      </Menu>
                    </div>
                  </div>
                  <div className="-mr-2 flex md:hidden">
                    {/* Mobile menu button */}
                    <Disclosure.Button className="inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                      <span className="sr-only">Open main menu</span>
                      {open ? (
                        <XIcon className="block h-6 w-6" aria-hidden="true" />
                      ) : (
                        <MenuIcon
                          className="block h-6 w-6"
                          aria-hidden="true"
                        />
                      )}
                    </Disclosure.Button>
                  </div>
                </div>
              </Container>

              <Disclosure.Panel className="md:hidden">
                <div className="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                  {navigation.map((item) => (
                    <Disclosure.Button
                      key={item.name}
                      as="a"
                      href={item.href}
                      className={classNames(
                        item.current
                          ? "bg-gray-900 text-white"
                          : "text-gray-300 hover:bg-gray-700 hover:text-white",
                        "block rounded-md px-3 py-2 text-base font-medium"
                      )}
                      aria-current={item.current ? "page" : undefined}
                    >
                      {item.name}
                    </Disclosure.Button>
                  ))}
                </div>
                <div className="border-t border-gray-700 pt-4 pb-3">
                  <div className="flex items-center px-5">
                    <div className="flex-shrink-0">
                      <img
                        className="h-10 w-10 rounded-full"
                        src={user.imageUrl}
                        alt=""
                      />
                    </div>
                    <div className="ml-3">
                      <div className="text-base font-medium leading-none text-white">
                        {user.name}
                      </div>
                      <div className="text-sm font-medium leading-none text-gray-400">
                        {user.email}
                      </div>
                    </div>
                    <button
                      type="button"
                      className="ml-auto flex-shrink-0 rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                    >
                      <span className="sr-only">View notifications</span>
                      <BellIcon className="h-6 w-6" aria-hidden="true" />
                    </button>
                  </div>
                  <div className="mt-3 space-y-1 px-2">
                    {userNavigation.map((item) => (
                      <Disclosure.Button
                        key={item.name}
                        as="a"
                        href={item.href}
                        className="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-gray-700 hover:text-white"
                      >
                        {item.name}
                      </Disclosure.Button>
                    ))}
                  </div>
                </div>
              </Disclosure.Panel>
            </>
          )}
        </Disclosure>

        {(props.header || props.title) && (
          <header className="bg-white shadow">
            <Container className="py-5">
              <Breadcrumbs crumbs={props.crumbs} />
              {props.header}

              {!props.header && (
                <h1 className="text-3xl font-bold text-gray-900">
                  {props.title}
                </h1>
              )}
            </Container>
          </header>
        )}
      </div>
    </>
  );
};

const BasicPageHeader = (props) => {
  return (
    <div className="bg-gray-200 text-gray-700  dark:bg-slate-900 dark:text-slate-200">
      <Container>
        <div className="py-3">
          <ApplicationLogo />
          <div className="mt-3 flex">
            <div className="mr-3">
              <Link href="/members">Members</Link>
            </div>
            <div className="mr-3">
              <Link href="/squads">Squads</Link>
            </div>
            <div className="mr-3">
              <Link href="/registers">Registers</Link>
            </div>
            <div className="mr-3">
              <Link href="/users">Users</Link>
            </div>
            <div className="mr-3">
              <Link href="/Payments">Payments</Link>
            </div>
            <div className="mr-3">
              <Link href="/notify">Notify</Link>
            </div>
            <div className="mr-3">
              <Link href="/galas">Galas</Link>
            </div>
            <div className="mr-3">
              <Link href="/admin">Admin</Link>
            </div>
            <div className="mr-3">
              <Link href="/account">Account</Link>
            </div>
          </div>
        </div>
      </Container>
    </div>
  );
};

export default PageHeader;
