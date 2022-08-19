import React from "react";
import { usePage } from "@inertiajs/inertia-react";

export default function ApplicationLogo({ className }) {
    const { tenant } = usePage().props;

    return <>{tenant.club_logo_url && <img src={`${tenant.club_logo_url}logo-75.png`} srcset={`${tenant.club_logo_url}logo-75@2x.png 2x, ${tenant.club_logo_url}logo-75@3x.png 3x`} />}</>;
}
