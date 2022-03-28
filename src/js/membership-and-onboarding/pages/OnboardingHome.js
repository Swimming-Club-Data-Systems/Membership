import React, { useEffect } from "react";
import { Link } from "react-router-dom";
import Header from "../../components/Header";
import Breadcrumb from "../../components/Breadcrumb";
import * as tenantFunctions from "../../classes/Tenant";
import Container from "../../components/Container";

const OnboardingHome = () => {

  const crumbs = [
    {
      to: "/onboarding",
      title: "Onboarding",
      name: "Onboarding",
    },
  ];

  useEffect(() => {
    tenantFunctions.setTitle("Onboarding");
  }, []);

  return (
    <>
      <Header title="Welcome to onboarding" subtitle="Onboarding is the replacement for assisted registration." breadcrumbs={<Breadcrumb crumbs={crumbs} />} />

      <Container>
        <p>
          Onboarding works hand in hand with the new Membership Centre feature lets clubs track which memberships their members hold in a given year.
        </p>

        <h2>Onboard a new user</h2>
        <p className="lead">Create and onboard a new user and associated members quickly and easily.</p>

        <p>
          <Link to="/onboarding/new" className="btn btn-primary">
            Get started
          </Link>
        </p>

        <h2>View all onboarding sessions</h2>
        <p className="lead">Find unfinished, pending or completed onboarding sessions.</p>

        <p>
          <Link to="/onboarding/all" className="btn btn-primary">
            View all
          </Link>
        </p>

        <h2>Complete onboarding</h2>
        <p className="lead">Sessions?</p>
      </Container>

    </>
  );
};

export default OnboardingHome;