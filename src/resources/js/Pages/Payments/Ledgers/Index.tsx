import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Collection from "@/Components/Collection";

type Props = {
    ledgers: [];
};

const ItemContent = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.name}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.type}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Payment Methods" />

            <Collection
                {...props.ledgers}
                itemRenderer={ItemContent}
                route="payments.ledgers.show"
            />
        </>
    );
};

Index.layout = (page) => (
    <MainLayout
        title="Ledgers"
        subtitle="Manage your custom ledgers"
        breadcrumbs={[
            { name: "Payments", route: "my_account.index" },
            { name: "Ledgers", route: "payments.ledgers.index" },
        ]}
    >
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Index;
