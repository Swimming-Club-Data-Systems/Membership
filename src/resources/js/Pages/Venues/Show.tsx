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

export type Props = {
    google_maps_api_key: string;
    name: string;
    id: number;
    formatted_address: string;
    place_id: string;
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

const Show: Layout<Props> = (props: Props) => {
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

            <Container noMargin>
                <MainHeader
                    title={props.name}
                    subtitle={props.formatted_address}
                ></MainHeader>

                <iframe
                    width="100%"
                    height="450"
                    style={{ border: 0 }}
                    loading="lazy"
                    allowFullScreen
                    referrerPolicy="no-referrer-when-downgrade"
                    src={`https://www.google.com/maps/embed/v1/place?key=${encodeURIComponent(
                        props.google_maps_api_key
                    )}&q=place_id:${encodeURIComponent(props.place_id)}`}
                ></iframe>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
