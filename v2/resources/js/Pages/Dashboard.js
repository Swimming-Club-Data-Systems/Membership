import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head } from "@inertiajs/inertia-react";

const Dashboard = (props) => {
  return (
    <>
      <Head title="Dashboard" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-200 bg-white p-6">
              Hey {`${props.auth.user.first_name} ${props.auth.user.last_name}`}
              ! You're logged in!
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

Dashboard.layout = (page) => (
  <Authenticated children={page} title="Dashboard" />
);

export default Dashboard;
