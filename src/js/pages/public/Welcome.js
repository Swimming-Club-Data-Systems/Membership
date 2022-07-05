import React from "react";
import * as tenantFunctions from "../../classes/Tenant";
import InternalContainer from "../../components/InternalContainer";
import Link from "../../components/Link";

const Welcome = () => {

  tenantFunctions.setTitle("Home");

  return (

    <>

      <InternalContainer>

        <div className="py-5">

          <div className="px-5 py-6 rounded-md bg-white shadow">
            <h1 className="text-indigo-600 font-bold text-3xl">
              Welcome to the {tenantFunctions.getName()} Membership System
            </h1>
            <p className="font-semibold text-2xl mb-4">
              Manage your members, payments and competition entries.
            </p>

            <p>
              <Link to="/login">
                Log in to your account
              </Link>
            </p>
          </div>

          <div className="px-5 py-6 rounded-md bg-white shadow my-5">
            <h2 className="font-semibold text-2xl mb-4">
              Just joined? Not got an account?
            </h2>

            <p className="mb-4">
              If you&apos;re a member, the club will create your account for you.
            </p>

            <p>
              If you&apos;ve just joined, the person handling your application and membership will be in touch with you soon with details about the next steps, which include setting up your club account.
            </p>
          </div>

          <div className="px-5 py-6 rounded-md bg-white shadow my-5">
            <h2 className="font-semibold text-2xl mb-4">
              Get SCDS Membership Software for your own club
            </h2>

            <p>
              FOLLOW A LINK
            </p>
          </div>

        </div>

      </InternalContainer>

    </>
  );
};

export default Welcome;