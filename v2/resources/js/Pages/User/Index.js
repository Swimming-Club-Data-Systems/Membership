import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Collection from "@/Components/Collection";

const ItemContent = (props) => {
  return (
    <>
      <div className="flex items-center justify-between">
        <div className="truncate text-sm font-medium text-indigo-600">
          {props.first_name} {props.last_name}
        </div>
        <div className="ml-2 flex flex-shrink-0">
          <span className="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
            Full-time
          </span>
        </div>
      </div>
    </>
  );
};

const Index = (props) => {
  console.log(props)
  return (
    <Authenticated
      auth={props.auth}
      errors={props.errors}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          Dashboard
        </h2>
      }
    >
      <Head title="Users" />

      <Container>
        <Collection {...props.users} itemRenderer={ItemContent} route="user.show" />
      </Container>
    </Authenticated>
  );
};

export default Index;
