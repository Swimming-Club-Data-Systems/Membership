import React, { useState } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link } from '@inertiajs/inertia-react';

export default function Authenticated({ auth, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="flex">
            <div className="basis-64 h-screen p-3">
                <div className="mb-3">
                    <Link href="/">
                        <ApplicationLogo className="block h-9 w-auto text-gray-500" />
                    </Link>
                </div>
                <hr />

                <div>
                    <div className="py-3">
                        <NavLink href={route('home')} active={route().current('home')}>
                            Home
                        </NavLink>

                        <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                            Dashboard
                        </NavLink>

                        <NavLink href={route('login')}>
                            Log In
                        </NavLink>

                        <NavLink href={route('register')}>
                            Register
                        </NavLink>

                        <NavLink href={route('logout')} method="post" as="button">
                            Log Out
                        </NavLink>
                    </div>
                </div>

            </div>
            <div className="grow">
                <div className="min-h-screen bg-gray-100">

                    {header && (
                        <header className="bg-white shadow">
                            <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
                        </header>
                    )}

                    <main>{children}</main>
                </div>
            </div>
        </div>
    );
}
