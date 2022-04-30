import React, { useEffect } from "react";
import Pagination from "./Pagination";
import { ExclamationIcon } from "@heroicons/react/outline";
import { Inertia } from "@inertiajs/inertia";
import Form from "./form/Form";
import TextInput from "./form/TextInput";
import * as yup from "yup";
import { useFormikContext } from "formik";
import Button from "./Button";
// import route from "vendor/tightenco/ziggy/src/js";

const Search = (props) => {
  const handleSubmit = (values) => {
    Inertia.get("/user/search", values);
  };

  const SetSearchValue = () => {
    const formikProps = useFormikContext();

    useEffect(() => {
      const params = new URLSearchParams(window.location.search);
      const searchValue = params.get("search");
      formikProps.setFieldValue("search", searchValue);
    }, []);

    return null;
  };

  const SearchButton = () => {
    const { isSubmitting, dirty, isValid } = useFormikContext();
    return (
      <Button
        type="submit"
        variant="primary"
        className="rounded-l-none"
        disabled={!dirty || !isValid || isSubmitting}
      >
        Search
      </Button>
    );
  };

  return (
    <Form
      onSubmit={handleSubmit}
      initialValues={{
        search: "",
      }}
      validationSchema={yup.object().shape({
        search: yup.string().required(""),
      })}
      hideDefaultButtons
    >
      <SetSearchValue />

      <div className="flex items-end">
        <div className="grow">
          <TextInput name="search" label="Search" className="rounded-r-none" />
        </div>
        <div className="flex-none pb-3">
          <SearchButton />
        </div>
      </div>
    </Form>
  );
};

const Collection = (props) => {
  const items = props.data.map((item, idx) => {
    return (
      <li key={item.id || idx}>
        <a href={route("user.show", item.id)} className="block hover:bg-gray-50">
          <div className="px-4 py-4 sm:px-6">{props.itemRenderer(item)}</div>
        </a>
      </li>
    );
  });

  return (
    <>
      <Search />

      {props.data.length > 0 && (
        <div className="overflow-hidden bg-white shadow sm:rounded-lg">
          <ul role="list" className=" divide-y divide-gray-200">
            {items}
          </ul>
          <Pagination collection={props} />
        </div>
      )}

      {props.data.length === 0 && (
        <div className="overflow-hidden bg-white px-4 pt-5 pb-4 shadow sm:rounded-lg sm:p-6 sm:pb-4">
          <div className="sm:flex sm:items-start">
            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <ExclamationIcon
                className="h-6 w-6 text-red-600"
                aria-hidden="true"
              />
            </div>
            <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 className="text-lg font-medium leading-6 text-gray-900">
                No results
              </h3>
              <div className="mt-2">
                <p className="text-sm text-gray-500">
                  We could not find any items to match your search.
                </p>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default Collection;
