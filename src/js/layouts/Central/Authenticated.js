import React, { useState } from "react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Dropdown from "@/Components/Dropdown";
import NavLink from "@/Components/NavLink";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink";
import { Link } from "react-router-dom";
import Footer from "@/Components/Footer";
import Container from "@/Components/Container";

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
