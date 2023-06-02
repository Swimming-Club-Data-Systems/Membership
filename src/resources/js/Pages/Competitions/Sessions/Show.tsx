import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Card from "@/Components/Card";
import {
    DefinitionList,
    DefinitionListItemProps,
} from "@/Components/DefinitionList";
import Button from "@/Components/Button";
import { RenderServerErrors } from "@/Components/Form/Form";
import FlashAlert from "@/Components/FlashAlert";
import BasicList from "@/Components/BasicList";

const getCategoryName = (category: string): string => {
    switch (category) {
        case "open":
            return "Open";
        case "male":
            return "Male";
        case "female":
            return "Female";
        case "mixed":
            return "Mixed";
        case "boy":
            return "Boys";
        case "girl":
            return "Girls";
    }
};

interface Event {
    session: number;
    id: number;
    name: string;
    ages: string[];
    category: string;
    created_at: string;
    updated_at: string;
    distance: number;
    entry_fee: number;
    entry_fee_string: string;
    event_code: number;
    processing_fee: number;
    processing_fee_string: string;
    sequence: number;
    stroke: string;
}

export type Props = {
    google_maps_api_key: string;
    name: string;
    id: number;
    competition: { name: string; id: number };
    venue: {
        name: string;
        id: number;
        formatted_address: string;
        place_id: string;
    };
    sequence_number: number;
    number_of_sessions: number;
    events: Event[];
};

const Event = (item: Event) => {
    const deleteEvent = (session, event) => {};

    return {
        id: item.id,
        content: (
            <>
                <div
                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                    key={item.id}
                >
                    <div className="">
                        <div className="text-gray-900">
                            <strong>{getCategoryName(item.category)}</strong>{" "}
                            {item.name}
                        </div>
                        <div className="text-gray-500">
                            <>
                                Age group{item.ages.length > 1 ? "s" : null}:{" "}
                                {item.ages.join(", ")}
                            </>
                        </div>
                        <div className="text-gray-500">
                            <>
                                £{item.entry_fee_string}
                                {item.processing_fee > 0 && (
                                    <>
                                        {" "}
                                        plus £{item.processing_fee_string}{" "}
                                        processing fee
                                    </>
                                )}
                            </>
                        </div>
                    </div>
                    {
                        <div className="block">
                            <>
                                <Button
                                    variant="danger"
                                    className="ml-3"
                                    onClick={() => {
                                        deleteEvent(item.session, item.id);
                                    }}
                                >
                                    Delete
                                </Button>
                            </>
                        </div>
                    }
                </div>
            </>
        ),
    };
};

const Show: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                    {
                        name: "Sessions",
                        route: "competitions.sessions.index",
                        routeParams: {
                            competition: props.id,
                        },
                    },
                    {
                        name: props.name,
                        route: "competitions.sessions.show",
                        routeParams: {
                            competition: props.competition.id,
                            session: props.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={props.name}
                    subtitle={`Session ${props.sequence_number} of ${props.number_of_sessions}`}
                ></MainHeader>

                <div className="grid grid-cols-12 gap-6">
                    <div className="col-start-1 col-span-7 flex flex-col gap-6">
                        <Card title="Events">
                            {/*<RenderServerErrors />*/}
                            <FlashAlert className="mb-4" bag="manage_lines" />

                            {props.events.length > 0 && (
                                <BasicList items={props.events.map(Event)} />
                            )}
                        </Card>
                    </div>
                    <div className="row-start-1 col-start-8 col-span-5">
                        <Card title="Venue" subtitle={props.venue.name}>
                            <p className="text-sm">
                                {props.venue.formatted_address}
                            </p>
                            <iframe
                                width="100%"
                                height="400"
                                style={{ border: 0 }}
                                loading="lazy"
                                allowFullScreen
                                referrerPolicy="no-referrer-when-downgrade"
                                src={`https://www.google.com/maps/embed/v1/place?key=${encodeURIComponent(
                                    props.google_maps_api_key
                                )}&q=place_id:${encodeURIComponent(
                                    props.venue.place_id
                                )}`}
                            ></iframe>
                        </Card>
                    </div>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
