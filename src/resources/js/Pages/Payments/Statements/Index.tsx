import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Collection from "@/Components/Collection";
import { formatDate } from "@/Utils/date-utils";

type Props = {
    statements: [];
};

type ItemProps = {
    start: string;
    end: string;
    closing_balance: number;
    closing_balance_formatted: string;
};

const ItemContent: React.FC<ItemProps> = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {formatDate(props.start)} - {formatDate(props.end)}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            Closing balance: {props.closing_balance_formatted}
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
            <Head title="Statements" />

            <Collection
                {...props.statements}
                itemRenderer={ItemContent}
                route="payments.statements.show"
            />
        </>
    );
};

Index.layout = (page) => {
    return (
        <MainLayout
            title="Statements"
            subtitle="View your account statements"
            breadcrumbs={[
                { name: "Payments", route: "my_account.index" },
                { name: "Statements", route: "payments.statements.index" },
            ]}
        >
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default Index;
