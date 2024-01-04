import React, { useEffect } from "react";
import Pagination from "./Pagination";
import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";
import { router } from "@inertiajs/react";
import Form from "./Form/Form";
import TextInput from "./Form/TextInput";
import * as yup from "yup";
import { useFormikContext } from "formik";
import Button from "./Button";
import InternalContainer from "@/Components/InternalContainer";
import EmptyState from "@/Components/EmptyState.js";

const Search = (props) => {
    const url = props.path;

    const handleSubmit = (values) => {
        router.get(url, values);
    };

    const SetSearchValue = () => {
        const formikProps = useFormikContext();

        useEffect(() => {
            const params = new URLSearchParams(window.location.search);
            const searchValue = params.get("query");
            if (searchValue) {
                formikProps.setFieldValue("query", searchValue);
            }
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
                disabled={!isValid || isSubmitting}
            >
                Search
            </Button>
        );
    };

    return (
        <div className="px-4 sm:px-0">
            <Form
                onSubmit={handleSubmit}
                initialValues={{
                    query: "",
                }}
                validationSchema={yup.object().shape({
                    query: yup.string().nullable().required(""),
                })}
                hideDefaultButtons
            >
                <SetSearchValue />

                <TextInput
                    name="query"
                    label="Search"
                    className="rounded-r-none mb-0"
                    rightButton={
                        // relative -ml-px inline-flex items-center space-x-2 rounded-r-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        <SearchButton />
                    }
                />
            </Form>
        </div>
    );
};

const PlainCollection = (props) => {
    const items = props.data.map((item, idx) => {
        return (
            <li key={item.id || idx}>
                <div className="py-4">
                    <InternalContainer>
                        {props.itemRenderer(item)}
                    </InternalContainer>
                </div>
            </li>
        );
    });

    return (
        <>
            {props.searchable && <Search path={props.path} />}

            {props.data.length > 0 && (
                <div className="overflow-hidden bg-white shadow sm:rounded-lg">
                    <div className="border-b border-gray-200 bg-white py-3">
                        <InternalContainer>
                            <p className="text-sm text-gray-700">
                                Page{" "}
                                <span className="font-medium">
                                    {props.current_page}
                                </span>{" "}
                                of{" "}
                                <span className="font-medium">
                                    {props.last_page}
                                </span>
                            </p>
                        </InternalContainer>
                    </div>
                    <ul role="list" className="divide-y divide-gray-200">
                        {items}
                    </ul>
                    <Pagination collection={props} />
                </div>
            )}

            {props.data.length === 0 && (
                <EmptyState>
                    <p>We could not find any items to match your search.</p>
                </EmptyState>
            )}
        </>
    );
};

export default PlainCollection;
