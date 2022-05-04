import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";

const Dashboard = (props) => {
  return (
    <>
      <Head title="Dashboard" />

      <Card>
        Hey {`${props.auth.user.first_name} ${props.auth.user.last_name}`}!
        You're logged in!
      </Card>
    </>
  );
};

Dashboard.layout = (page) => (
  <Authenticated children={page} title="Dashboard" />
);

export default Dashboard;
