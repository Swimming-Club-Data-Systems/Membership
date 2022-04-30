import React, { useState } from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Dropdown from "@/Components/Dropdown";
import NavLink from "@/Components/NavLink";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink";
import { usePage, Link } from "@inertiajs/inertia-react";
import Footer from "@/Components/Footer";
import Container from "@/Components/Container";
import PageHeader from "@/Components/PageHeader";

export default function Authenticated({ children, ...otherProps }) {
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
}
