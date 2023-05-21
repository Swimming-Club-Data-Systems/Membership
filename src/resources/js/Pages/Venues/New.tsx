import React, { useEffect, useRef } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import { Status, Wrapper } from "@googlemaps/react-wrapper";
import Alert from "@/Components/Alert";
import { useFormikContext } from "formik";

export type Props = {
    google_maps_api_key: string;
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

const New: Layout<Props> = (props: Props) => {
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
                title="New Venue"
                breadcrumbs={[
                    { name: "Venues", route: "venues.index" },
                    {
                        name: "New",
                        route: "venues.index",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Create Venue"}
                    subtitle={`Create a new venue`}
                ></MainHeader>

                <Form
                    validationSchema={yup.object().shape({})}
                    initialValues={{
                        search: "",
                        address_components: [],
                        long: "",
                        lat: "",
                        name: "",
                        phone: "",
                        website: "",
                        google_maps_url: "",
                        vicinity: "",
                        place_id: "",
                        plus_code_global: "",
                        plus_code_compound: "",
                        formatted_address: "",
                        html_attributions: [],
                    }}
                    submitTitle="Create"
                    action={route("venues.index")}
                    method="post"
                >
                    <Wrapper
                        apiKey={props.google_maps_api_key}
                        render={render}
                        libraries={["places"]}
                    >
                        <MapComponent />
                    </Wrapper>

                    <TextInput
                        name="formatted_address"
                        label="Formatted address"
                    />
                    <TextInput name="long" label="Longitude" />
                    <TextInput name="lat" label="Latitude" />
                    <TextInput name="name" label="Name" />
                    <TextInput name="phone" label="Phone number" />
                    <TextInput name="website" label="Website" />
                    <TextInput name="url" label="URL" />
                    <TextInput name="vicinity" label="Vicinity" />
                    <TextInput name="place_id" label="Place ID" />
                    <TextInput
                        name="plus_code.compound_code"
                        label="Plus Code (Compound)"
                    />
                    <TextInput
                        name="plus_code.global_code"
                        label="Plus Code (Global)"
                    />
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
