import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import Collection from "@/Components/Collection";
import { Head } from "@inertiajs/react";

const ItemContent = (props) => {
    const logo = props.logo_path
        ? `${props.logo_path}icon-72x72.png`
        : "/img/corporate/scds.svg";

    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div className="mr-4">
                        <img
                            className="h-8 w-8 rounded-full"
                            src={logo}
                            alt=""
                        />
                    </div>
                    <div>
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.Name}
                        </div>
                        {props.Code && (
                            <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                                {props.Code}
                            </div>
                        )}
                    </div>
                </div>
                {/*<div className="ml-2 flex flex-shrink-0">*/}
                {/*    <span className="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">*/}
                {/*        Active*/}
                {/*    </span>*/}
                {/*</div>*/}
            </div>
        </>
    );
};

const Clubs = (props) => {
    return (
        <Container noMargin>
            <Head title="Clubs" />
            <Collection
                searchable
                {...props.tenants}
                itemRenderer={(item) => <ItemContent {...item} />}
                route="central.clubs.redirect"
                routeIdName="ID"
            />
        </Container>
    );
};

Clubs.layout = (page) => (
    <CentralMainLayout title="Clubs" subtitle="Find your club to sign in">
        {page}
    </CentralMainLayout>
);

export default Clubs;
