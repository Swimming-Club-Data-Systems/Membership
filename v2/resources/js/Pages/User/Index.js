import React from 'react';
import Authenticated from '@/Layouts/Tenant/Authenticated';
import { Head } from '@inertiajs/inertia-react';
import Container from '@/Components/Container';
import Collection from '@/Components/Collection';

const ItemContent = (props) => {
  return (
    <>
      <div className="flex items-center justify-between">
        <div className="text-sm font-medium text-indigo-600 truncate">
          {props.first_name} {props.last_name}
        </div>
        <div className="ml-2 flex-shrink-0 flex">
          <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
            Full-time
          </span>
        </div>
      </div>
    </>
  );
}

const Index = (props) => {
  return (
    <Authenticated
      auth={props.auth}
      errors={props.errors}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>}
    >
      <Head title="Users" />

      <Container>
        <Collection
          {...props.users}
          itemRenderer={ItemContent}
        />
      </Container>
    </Authenticated>
  );
}

export default Index;