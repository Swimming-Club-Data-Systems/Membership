import React from "react";
import { Head } from "@inertiajs/inertia-react";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Form from "@/Components/form/Form";
import TextInput from "@/Components/form/TextInput";
import Checkbox from "@/Components/form/Checkbox";
import Radio from "@/Components/form/Radio";
import Fieldset from "@/Components/form/Fieldset";
import Container from "@/Components/Container";
import Footer from "@/Components/Footer";
import CodeEditor from "@/Components/CodeEditor";
import Link from "@/Components/Link";
import Button from "@/Components/Button";
import Header from "@/Components/Header";

export default function Home(props) {
  return (
    <>
      <Head>
        <title>Form Components - Membership Software Development</title>
        <meta
          name="description"
          content="Membership Software for Swimming Clubs by SCDS"
        />
      </Head>

      <Header title="Formik + Form Components" subtitle="Demo and Scratchpad" />

      <Container>
        <Form
          initialValues={{
            name: "",
          }}
        >
          <div className="bg-gray-100 shadow dark:bg-slate-800 dark:text-gray-100 sm:overflow-hidden  sm:rounded-md">
            <div className="space-y-6 bg-white px-4 py-5 sm:p-6">
              <div className="grid grid-cols-3 gap-6">
                <div className="col-span-3 sm:col-span-2">
                  <label
                    htmlFor="company-website"
                    className="block text-sm font-medium text-gray-700"
                  >
                    Website
                  </label>
                  <div className="mt-1 flex rounded-md shadow-sm">
                    <span className="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                      http://
                    </span>
                    <input
                      type="text"
                      name="company-website"
                      id="company-website"
                      className="block w-full flex-1 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      placeholder="www.example.com"
                    />
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-6 gap-6">
                <div className="col-span-6 sm:col-span-3">
                  <label
                    htmlFor="first-name"
                    className="block text-sm font-medium text-gray-700"
                  >
                    First name
                  </label>
                  <input
                    type="text"
                    name="first-name"
                    id="first-name"
                    autoComplete="given-name"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>

                <div className="col-span-6 sm:col-span-3">
                  <TextInput
                    label="Name of user"
                    name="name"
                    //type="text"
                  />
                </div>
              </div>

              {/* <div>
                  <TextInput
                    label="Name of user"
                    name="name"
                  />
                </div> */}

              <div>
                <label
                  htmlFor="about"
                  className="block text-sm font-medium text-gray-700"
                >
                  About
                </label>
                <div className="mt-1">
                  <textarea
                    id="about"
                    name="about"
                    rows={3}
                    className="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="you@example.com"
                    defaultValue={""}
                  />
                </div>
                <p className="mt-2 text-sm text-gray-500">
                  Brief description for your profile. URLs are hyperlinked.
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Photo
                </label>
                <div className="mt-1 flex items-center">
                  <span className="inline-block h-12 w-12 overflow-hidden rounded-full bg-gray-100">
                    <svg
                      className="h-full w-full text-gray-300"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </span>
                  <button
                    type="button"
                    className="ml-5 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  >
                    Change
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Cover photo
                </label>
                <div className="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6">
                  <div className="space-y-1 text-center">
                    <svg
                      className="mx-auto h-12 w-12 text-gray-400"
                      stroke="currentColor"
                      fill="none"
                      viewBox="0 0 48 48"
                      aria-hidden="true"
                    >
                      <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        strokeWidth={2}
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      />
                    </svg>
                    <div className="flex text-sm text-gray-600">
                      <label
                        htmlFor="file-upload"
                        className="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500"
                      >
                        <span>Upload a file</span>
                        <input
                          id="file-upload"
                          name="file-upload"
                          type="file"
                          className="sr-only"
                        />
                      </label>
                      <p className="pl-1">or drag and drop</p>
                    </div>
                    <p className="text-xs text-gray-500">
                      PNG, JPG, GIF up to 10MB
                    </p>
                  </div>
                </div>
              </div>

              <Fieldset legend="Choose some checkboxes">
                <Checkbox
                  name="check"
                  label="Check me"
                  help="Get notified when someones posts a comment on a posting."
                />
              </Fieldset>

              <Fieldset legend="Choose a radio button">
                <Radio
                  name="radio"
                  value="1"
                  label="I'm a radio"
                  help="Get notified when someones posts a comment on a posting."
                />
                <Radio
                  name="radio"
                  value="2"
                  label="I'm a different radio"
                  help="Get notified when someones posts a comment on a posting."
                />
              </Fieldset>

              <CodeEditor
                language="javascript"
                value={`let x = 1;
x = x + 1;
console.log(x);`}
              />

              <Link href="/">Link to home</Link>

              <div className="py-3">
                <Button variant="primary">Primary</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="danger">Danger</Button>
                <Button variant="warning">Warning</Button>
              </div>
            </div>

            <div className="bg-gray-50 px-4 py-3 text-right sm:px-6">
              <button
                type="submit"
                className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                Save
              </button>
            </div>
          </div>
        </Form>
      </Container>

      <Footer />
    </>
  );
}
