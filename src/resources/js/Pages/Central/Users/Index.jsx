import React from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import Collection from "@/Components/Collection";
import CentralMainLayout from "@/Layouts/CentralMainLayout";

const ItemContent = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="mr-4 shrink-0">
                        <img
                            className="h-8 w-8 rounded-full"
                            src={props.gravatar_url}
                            alt=""
                        />
                    </div>
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.Forename} {props.Surname}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.EmailAddress}
                        </div>
                    </div>
                </div>
                {/*<div className="ml-2 flex-none flex-shrink-0">*/}
                {/*    <span className="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">*/}
                {/*        Active*/}
                {/*    </span>*/}
                {/*</div>*/}
            </div>
        </>
    );
};

const Index = (props) => {
    return (
        <>
            <Head title="Users" />

            <Container noMargin>
                <Collection
                    {...props.users}
                    itemRenderer={ItemContent}
                    route="users.show"
                    routeIdName="UserID"
                />
            </Container>
        </>
    );
};

const crumbs = [{ href: "/users", name: "Users" }];

Index.layout = (page) => (
    <CentralMainLayout title="Users" subtitle="User list">
        {page}
    </CentralMainLayout>
);

export default Index;
