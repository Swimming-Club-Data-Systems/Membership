import React, { useEffect, useState } from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/react/20/solid";
import { Combobox } from "@headlessui/react";
import axios from "@/Utils/axios";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

const ComboboxComponent = () => {
    const [query, setQuery] = useState("");
    const [items, setItems] = useState([]);
    const [selectedPerson, setSelectedPerson] = useState(null);

    const compareItems = (a, b) => {
        return a?.id === b?.id;
    };

    useEffect(() => {
        // Get items
        // /component-testing-user-search?query=blah
        axios
            .get("/component-testing-user-search", {
                params: {
                    query: query,
                },
            })
            .then((value) => {
                setItems(value.data.data);
            });
    }, [query]);

    const filteredPeople = items;
    // query === ""
    //     ? people
    //     : people.filter((person) => {
    //           return person.name
    //               .toLowerCase()
    //               .includes(query.toLowerCase());
    //       });

    return (
        <Combobox
            as="div"
            value={selectedPerson}
            onChange={setSelectedPerson}
            by={compareItems}
        >
            <Combobox.Label className="block text-sm font-medium text-gray-700">
                Assigned to
            </Combobox.Label>
            <div className="relative mt-1">
                <Combobox.Input
                    className="w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                    onChange={(event) => setQuery(event.target.value)}
                    displayValue={(person) => person?.name}
                />
                <Combobox.Button className="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-none">
                    <ChevronUpDownIcon
                        className="h-5 w-5 text-gray-400"
                        aria-hidden="true"
                    />
                </Combobox.Button>

                {filteredPeople.length > 0 && (
                    <Combobox.Options className="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                        {filteredPeople.map((person) => (
                            <Combobox.Option
                                key={person.id}
                                value={person}
                                className={({ active }) =>
                                    classNames(
                                        "relative cursor-default select-none py-2 pl-3 pr-9",
                                        active
                                            ? "bg-indigo-600 text-white"
                                            : "text-gray-900"
                                    )
                                }
                            >
                                {({ active, selected }) => (
                                    <>
                                        <div className="flex items-center">
                                            <img
                                                src={person.gravitar_url}
                                                alt=""
                                                className="h-6 w-6 flex-shrink-0 rounded-full"
                                            />
                                            <span
                                                className={classNames(
                                                    "ml-3 truncate",
                                                    selected && "font-semibold"
                                                )}
                                            >
                                                {person.name}
                                            </span>
                                        </div>

                                        {selected && (
                                            <span
                                                className={classNames(
                                                    "absolute inset-y-0 right-0 flex items-center pr-4",
                                                    active
                                                        ? "text-white"
                                                        : "text-indigo-600"
                                                )}
                                            >
                                                <CheckIcon
                                                    className="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        )}
                                    </>
                                )}
                            </Combobox.Option>
                        ))}
                    </Combobox.Options>
                )}
            </div>
        </Combobox>
    );
};

type Props = {
    statements: [];
};

const ComponentTesting: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Component Testing" />

            <ComboboxComponent />
        </>
    );
};

ComponentTesting.layout = (page) => {
    return (
        <MainLayout
            title="Component Testing"
            subtitle="Testing new components"
            breadcrumbs={[]}
        >
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default ComponentTesting;
