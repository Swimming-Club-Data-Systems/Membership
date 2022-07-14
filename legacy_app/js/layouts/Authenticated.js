import React, { useState } from "react";
import ApplicationLogo from "../components/ApplicationLogo";
import Dropdown from "../components/Dropdown";
import NavLink from "../components/NavLink";
import ResponsiveNavLink from "../components/ResponsiveNavLink";
// import { Link } from "@inertiajs/inertia-react";
import Footer from "../components/Footer";
import Container from "../components/Container";

export default function Authenticated({ auth, children }) {
  const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

  return (
    <>
      <div className="bg-gray-100">
        <Container>
          <main>{children}</main>
        </Container>
      </div>
      <Footer />
    </>
  );
}
