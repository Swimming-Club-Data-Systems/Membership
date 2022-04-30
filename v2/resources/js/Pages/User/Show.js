import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head, usePage } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";

const Show = (props) => {
  return (
    <>
      <Head title="Users" />

      <Container>
        <pre>{JSON.stringify(props.user, null, 2)}</pre>
      </Container>
    </>
  );
};

Show.layout = (page) => (
  <Authenticated
    children={page}
    title={`${page.props.user.first_name} ${page.props.user.last_name}`}
    crumbs={[
      { href: "/users", name: "Users" },
      {
        href: route("user.show", page.props.user.id),
        name: `${page.props.user.first_name} ${page.props.user.last_name}`,
      },
    ]}
  />
);

export default Show;
