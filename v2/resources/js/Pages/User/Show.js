import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head, usePage } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Card from "@/Components/Card";
import { Dl, Dt, Dd } from "@/Components/DefinitionList";
import Alert from "@/Components/Alert";

const Show = (props) => {
  return (
    <>
      <Head title="Users" />

      {props.auth.user.id === props.user.id && (
        <Alert variant="success" title="Heads up!" className="mb-4">This user is you!</Alert>
      )}

      <Card header={<>Basic Details</>} className="mb-4">
        <Dl>
          <div className="sm:col-span-1">
            <Dt>Full name</Dt>
            <Dd>{`${props.user.first_name} ${props.user.last_name}`}</Dd>
          </div>
          <div className="sm:col-span-1">
            <Dt>Email</Dt>
            <Dd>
              <a href={`mailto:${props.user.email}`}>{props.user.email}</a>
            </Dd>
          </div>
          <div className="sm:col-span-1">
            <Dt>Phone</Dt>
            <Dd>
              {props.user.phone && (
                <a href={props.user.phone.link_format}>
                  {props.user.phone.local_or_national}
                </a>
              )}
              {!props.user.phone && (
                <>This user does not have a phone on file</>
              )}
            </Dd>
          </div>
        </Dl>
      </Card>

      <Card header={<>Metadata</>} className="mb-4">
        <Dl>
          <div className="sm:col-span-1">
            <Dt>Created at</Dt>
            <Dd>{props.user.created_at}</Dd>
          </div>
          <div className="sm:col-span-1">
            <Dt>Updated at</Dt>
            <Dd>{props.user.updated_at}</Dd>
          </div>
          <div className="sm:col-span-1">
            <Dt>Email verified at</Dt>
            <Dd>{props.user.email_verified_at}</Dd>
          </div>
        </Dl>
      </Card>

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
