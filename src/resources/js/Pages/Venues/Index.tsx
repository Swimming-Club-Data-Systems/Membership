import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import ButtonLink from "@/Components/ButtonLink";

interface VenueProps {
    id: number;
    name: string;
    formatted_address: string;
}

export type Props = {
    google_maps_api_key: string;
    venues: LaravelPaginatorProps<VenueProps>;
    can_create: boolean;
};

const VenueRenderer = (props: VenueProps): JSX.Element => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.name}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.formatted_address}
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
            <Head
                title="Venues"
                breadcrumbs={[{ name: "Venues", route: "venues.index" }]}
            />

            <Container>
                <MainHeader
                    title={"Venues"}
                    subtitle={`Venues for competitions and training sessions`}
                    buttons={
                        props.can_create && (
                            <ButtonLink href={route("venues.new")}>
                                New
                            </ButtonLink>
                        )
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
                <Collection
                    searchable
                    {...props.venues}
                    route="venues.show"
                    itemRenderer={(item) => <VenueRenderer {...item} />}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
