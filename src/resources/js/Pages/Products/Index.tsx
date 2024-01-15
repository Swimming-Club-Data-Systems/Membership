import React from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import Collection from "@/Components/Collection";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import ButtonLink from "@/Components/ButtonLink";
import FlashAlert from "@/Components/FlashAlert";

const ItemContent = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.name}
                        </div>
                        {/*<div className="truncate text-sm text-gray-700 group-hover:text-gray-800">*/}
                        {/*  */}
                        {/*</div>*/}
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

const crumbs = [{ route: "products.index", name: "Products" }];

const Index = (props) => {
    return (
        <>
            <Head title="Products" />

            <Container>
                <MainHeader
                    title="Products"
                    subtitle="Product list"
                    buttons={
                        <ButtonLink href={route("products.new")}>
                            New
                        </ButtonLink>
                    }
                />

                <FlashAlert className="mb-4" />
            </Container>

            <Container noMargin>
                <Collection
                    searchable
                    {...props.products}
                    itemRenderer={ItemContent}
                    route="products.show"
                />
            </Container>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout hideHeader breadcrumbs={crumbs}>
        {page}
    </MainLayout>
);

export default Index;
