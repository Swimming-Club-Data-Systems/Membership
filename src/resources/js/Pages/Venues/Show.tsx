import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { DefinitionList } from "@/Components/DefinitionList";
import Link from "@/Components/Link";

export type Props = {
    google_maps_api_key: string;
    name: string;
    id: number;
    formatted_address: string;
    place_id: string;
    website?: string;
    phone?: {
        formatted: string;
        url: string;
    };
    google_maps_url?: string;
};

const Show: Layout<Props> = (props: Props) => {
    const items = [];
    if (props.website) {
        items.push({
            key: "website",
            term: "Website",
            definition: (
                <Link external href={props.website}>
                    {props.website}
                </Link>
            ),
            truncate: true,
        });
    }

    if (props.phone) {
        items.push({
            key: "phone",
            term: "Phone",
            definition: (
                <Link external href={props.phone.url}>
                    {props.phone.formatted}
                </Link>
            ),
        });
    }

    if (props.google_maps_url) {
        items.push({
            key: "google_maps_url",
            term: "Google Maps",
            definition: (
                <Link external href={props.google_maps_url}>
                    View on Google Maps
                </Link>
            ),
        });
    }

    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Venues", route: "venues.index" },
                    {
                        name: props.name,
                        route: "venues.show",
                        routeParams: {
                            venue: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle={props.formatted_address}
                ></MainHeader>

                <div className="grid gap-4">
                    <div className="md:col-start-1 md:col-span-6">
                        <DefinitionList items={items} />
                    </div>
                    <div className="md:col-start-7">
                        <iframe
                            title="Map view"
                            width="100%"
                            height="450"
                            style={{ border: 0 }}
                            loading="lazy"
                            allowFullScreen
                            referrerPolicy="no-referrer-when-downgrade"
                            src={`https://www.google.com/maps/embed/v1/place?key=${encodeURIComponent(
                                props.google_maps_api_key
                            )}&q=place_id:${encodeURIComponent(
                                props.place_id
                            )}`}
                        ></iframe>
                    </div>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
