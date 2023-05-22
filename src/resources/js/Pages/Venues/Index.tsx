import React, { useEffect, useRef } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import TextInput from "@/Components/Form/TextInput";
import { Status } from "@googlemaps/react-wrapper";
import Alert from "@/Components/Alert";
import { useFormikContext } from "formik";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import { formatDateTime } from "@/Utils/date-utils";
import ButtonLink from "@/Components/ButtonLink";

interface VenueProps {
    id: number;
    name: string;
    formatted_address: string;
}

interface Venues extends LaravelPaginatorProps {
    data: VenueProps[];
}

export type Props = {
    google_maps_api_key: string;
    venues: Venues;
};

const MapComponent: React.FC = () => {
    const ref = useRef();

    const autocomplete = useRef(null);

    const { setFieldValue, setFieldTouched, validateField } =
        useFormikContext();

    const autocompleteChanged = () => {
        const place = autocomplete.current.getPlace();

        setFieldValue("name", place.name);
        setFieldValue("formatted_address", place.formatted_address);
        setFieldValue("vicinity", place.vicinity);
        setFieldValue("website", place.website);
        setFieldValue("plus_code_global", place.plus_code.global_code);
        setFieldValue("plus_code_compound", place.plus_code.compound_code);
        setFieldValue("place_id", place.place_id);
        setFieldValue("long", place.geometry.location.lng());
        setFieldValue("lat", place.geometry.location.lat());
        setFieldValue("phone", place.international_phone_number);
        setFieldValue("google_maps_url", place.url);
        setFieldValue("address_components", place.address_components);
        setFieldValue("html_attributions", place.html_attributions);
    };

    useEffect(() => {
        const options = {
            componentRestrictions: { country: "gb" },
            fields: [
                "address_components",
                "geometry",
                "name",
                "international_phone_number",
                "url",
                "website",
                "vicinity",
                "place_id",
                "plus_code",
                "formatted_address",
            ],
            strictBounds: false,
            types: ["establishment"],
        };

        autocomplete.current = new google.maps.places.Autocomplete(
            ref.current,
            options
        );
        autocomplete.current.addListener("place_changed", autocompleteChanged);
    }, []);

    return <TextInput ref={ref} name="search" label="Search" />;
    // return <div ref={ref} id="map" className="h-64" />;
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
    const render = (status) => {
        switch (status) {
            case Status.LOADING:
                return <>Loading</>;
            case Status.FAILURE:
                return (
                    <Alert title="Maps can not be loaded" variant="danger">
                        Sorry, but we&apos;re currently unable to load the map.
                    </Alert>
                );
            case Status.SUCCESS:
                return <MapComponent />;
        }
    };

    return (
        <>
            <Head
                title="Venues"
                breadcrumbs={[{ name: "Venues", route: "venues.index" }]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Venues"}
                    subtitle={`Venues for competitions and training sessions`}
                    buttons={
                        <ButtonLink href={route("venues.new")}>New</ButtonLink>
                    }
                ></MainHeader>

                <Collection
                    searchable
                    {...props.venues}
                    route="venues.show"
                    itemRenderer={VenueRenderer}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
