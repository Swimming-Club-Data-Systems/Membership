import React from 'react';
import { Link } from '@inertiajs/inertia-react';

export default function NavLink({ href, active, children, ...otherProps }) {
    return (
        <Link
            href={href}
            className={
                active
                    ? 'flex p-2 rounded bg-gray-100 w-full mb-2'
                    : 'flex p-2 rounded hover:bg-indigo-100 focus:bg-indigo-200 transition duration-150 ease-in-out w-full mb-2'
            }
            {...otherProps}
        >
            {children}
        </Link>
    );
}
