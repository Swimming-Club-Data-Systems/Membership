import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Collection from "@/Components/Collection";
import MainHeader from "@/Layouts/Components/MainHeader";
import ButtonLink from "@/Components/ButtonLink";

type JournalProps = {
    id: number;
    name: string;
    type: string;
};

type Props = {
    journals: [];
    name: string;
    type: string;
    id: number;
};

const ItemContent: React.FC<JournalProps> = (props) => {
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

const Show: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    { name: "Ledgers", route: "payments.ledgers.index" },
                    {
                        name: props.name,
                        route: "payments.ledgers.show",
                        routeParams: props.id,
                    },
                ]}
            />

            <MainHeader
                title={props.name}
                subtitle="Manage your custom ledgers"
                buttons={
                    <ButtonLink
                        href={route("payments.ledgers.journals.new", props.id)}
                    >
                        Create journal
                    </ButtonLink>
                }
            ></MainHeader>

            <Collection
                {...props.journals}
                itemRenderer={ItemContent}
                route="payments.ledgers.journals.show"
            />
        </>
    );
};

Show.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Show;
