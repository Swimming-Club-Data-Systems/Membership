import React, { useEffect } from "react";
import Pagination from "./Pagination";
import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
import { Link, router } from "@inertiajs/react";
import Form from "./Form/Form";
import TextInput from "./Form/TextInput";
import * as yup from "yup";
import { useFormikContext } from "formik";
import Button from "./Button";
import InternalContainer from "@/Components/InternalContainer";

type SearchProps = {
    path: string;
};

const Search: React.FC<SearchProps> = (props) => {
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

type CollectonProps = {
    route: string;
    data: [];
    routeIdName?: string;
    routeParams?: [number | string];
    searchable?: boolean;
    path: string;
    current_page: number;
    last_page: number;
    itemRenderer: (item: never) => React.FC;
};

const Collection: React.FC<CollectonProps> = (props) => {
    const items = props.data.map((item, idx) => {
        const routeIdName = props.routeIdName || "id";

        const routeParams = [...props.routeParams] || [];

        routeParams.push(item[routeIdName]);

        return (
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            <li key={item.id || idx}>
                <Link
                    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                    // @ts-ignore
                    href={route(props.route, routeParams)}
                    className="group block hover:bg-gray-50"
                >
                    <div className="py-4">
                        <InternalContainer>
                            {props.itemRenderer(item)}
                        </InternalContainer>
                    </div>
                </Link>
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
                <>
                    <div className="overflow-hidden bg-white px-4 pt-5 pb-4 shadow sm:p-6 sm:pb-4 lg:rounded-lg">
                        <div className="sm:flex sm:items-start">
                            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <ExclamationTriangleIcon
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
                                        We could not find any items to match
                                        your search.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    );
};

export default Collection;
