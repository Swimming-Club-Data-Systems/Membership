import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import ButtonLink from "@/Components/ButtonLink";

type LedgerProps = {
    id: number;
    name: string;
    type: string;
};

type Props = {
    ledgers: LaravelPaginatorProps<LedgerProps>;
};

const ItemContent = (props: LedgerProps) => {
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

Index.layout = (page) => {
    return (
        <MainLayout
            title="Ledgers"
            subtitle="Manage your custom ledgers"
            breadcrumbs={[
                { name: "Payments", route: "my_account.index" },
                { name: "Ledgers", route: "payments.ledgers.index" },
            ]}
            buttons={
                <ButtonLink href={route("payments.ledgers.new")}>
                    Create ledger
                </ButtonLink>
            }
        >
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default Index;
