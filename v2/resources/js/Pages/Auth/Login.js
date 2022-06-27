import React, { useEffect } from "react";
import Button from "@/Components/Button";
import Checkbox from "@/Components/form/Checkbox";
import Guest from "@/Layouts/Guest";
import Input from "@/Components/Input";
import Label from "@/Components/Label";
import ValidationErrors from "@/Components/ValidationErrors";
import { Head, useForm } from "@inertiajs/inertia-react";
import Link from "@/Components/Link";
import Form from "@/Components/form/Form";
import TextInput from "@/Components/form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";
import ApplicationLogo from "@/Components/ApplicationLogo";

export default function Login({ status, canResetPassword }) {
  const onSubmit = (values, formikBag) => {
    Inertia.post(route("login"), values, {
      onSuccess: (arg) => console.log(arg),
    });
  };

  return (
    <>
      <Head title="Sign in to your account" />
      <div className="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div className="sm:mx-auto sm:w-full sm:max-w-md">
          <ApplicationLogo className="mx-auto h-12 w-auto" />
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Sign in to your account
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Or <Link className="font-medium">find out how to join us</Link>
          </p>
        </div>

        <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
          <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <Form
              initialValues={{
                email: "",
                password: "",
                remember: true,
              }}
              validationSchema={yup.object().shape({
                email: yup
                  .string()
                  .required("An email address is required")
                  .email("Your email address must be valid"),
                password: yup.string().required("A password is required"),
                remember: yup
                  .boolean()
                  .oneOf(
                    [false, true],
                    "Remember me must be ticked or not ticked"
                  ),
              })}
              onSubmit={onSubmit}
              submitTitle="Sign in"
              submitClass="w-full"
              hideClear
            >
              <TextInput
                label="Email"
                name="email"
                type="email"
                autoComplete="username"
              />

              <TextInput
                label="Password"
                name="password"
                type="password"
                autoComplete="current-password"
              />

              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <Checkbox name="remember" label="Remember me" />
                </div>

                {canResetPassword && (
                  <div className="text-sm mb-3">
                    <Link href={route("password.request")}>
                      Forgot your password?
                    </Link>
                  </div>
                )}
              </div>
            </Form>
          </div>
        </div>
      </div>
    </>
  );
}
