import React from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import Collection from "@/Components/Collection";
import MainLayout from "@/Layouts/MainLayout";

const ItemContent = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.SquadName}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            £{props.SquadFee} / month
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
            <Head title="Squads" />

            <Container noMargin>
                <Collection
                    searchable
                    {...props.squads}
                    itemRenderer={ItemContent}
                    route="squads.show"
                    routeIdName="SquadID"
                />
            </Container>
        </>
    );
};

const crumbs = [{ route: "squads.index", name: "Squads" }];

Index.layout = (page) => (
    <MainLayout title="Squads" subtitle="Squad list" breadcrumbs={crumbs}>
        {page}
    </MainLayout>
);

export default Index;
