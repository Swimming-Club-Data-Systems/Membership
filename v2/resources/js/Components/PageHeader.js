import { Link } from "@inertiajs/inertia-react";
import React from "react";
import ApplicationLogo from "./ApplicationLogo";
import Container from "./Container";

const PageHeader = (props) => {
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
