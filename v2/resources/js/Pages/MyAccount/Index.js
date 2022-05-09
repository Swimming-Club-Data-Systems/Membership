import React from "react";
import Authenticated from "@/Layouts/Tenant/Authenticated";
import { Head, usePage } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";
import Form, { SubmissionButtons } from "@/Components/form/Form";
import TextInput from "@/Components/form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";
import "yup-phone";

const Index = (props) => {
  const onSubmit = (values, formikBag) => {
    Inertia.put(route("myaccount.update"), values, {
      onSuccess: (arg) => console.log(arg),
    });
    // formikBag.
  };

  return (
    <>
      <Head title="My Account" />

      <Form
        initialValues={{
          first_name: props.user.first_name,
          last_name: props.user.last_name,
          email: props.user.email,
          phone: props.user.phone?.number,
        }}
        validationSchema={yup.object().shape({
          first_name: yup
            .string()
            .max(255, "The maximum length for a name is 255 characters")
            .required("A first name is required"),
          last_name: yup
            .string()
            .max(255, "The maximum length for a name is 255 characters")
            .required("A last name is required"),
          email: yup
            .string()
            .email("Please enter a valid email address")
            .required("An email address is required"),
          phone: yup
            .string()
            .phone(undefined, undefined, "Please enter a valid phone number")
            .required("A phone number is required"),
        })}
        submitTitle="Save"
        onSubmit={onSubmit}
        hideDefaultButtons
      >
        <Card footer={<SubmissionButtons />}>
          <div className="grid grid-cols-2 gap-4">
            <TextInput name="first_name" label="First name" />

            <TextInput name="last_name" label="Last name" />
          </div>

          <TextInput name="email" label="Email address" type="email" />

          <TextInput name="phone" label="Phone number" type="tel" />
        </Card>
      </Form>
    </>
  );
};

Index.layout = (page) => (
  <Authenticated
    children={page}
    title="My Account"
    crumbs={[{ href: route("myaccount.index"), name: "My Account" }]}
  />
);

export default Index;
