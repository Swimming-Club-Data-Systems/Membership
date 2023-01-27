import React from "react";
import { usePage } from "@inertiajs/react";

export default function TenantLogo({ className }) {
    const { tenant } = usePage().props;

    return (
        <>
            {tenant.club_logo_url && (
                <img
                    // className="max-w-full max-h-full"
                    className={className}
                    src={`${tenant.club_logo_url}logo-75.png`}
                    srcSet={`${tenant.club_logo_url}logo-75@2x.png 2x, ${tenant.club_logo_url}logo-75@3x.png 3x`}
                    alt={tenant.club_name}
                />
            )}
        </>
    );
}
