import React, { useCallback, useEffect, useRef } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import { Status, Wrapper } from "@googlemaps/react-wrapper";
import Alert from "@/Components/Alert";
import { useFormikContext } from "formik";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";

export type Props = {
    google_maps_api_key: string;
};

const MapComponent: React.FC = () => {
    const ref = useRef();

    const autocomplete = useRef(null);

    const { setFieldValue } = useFormikContext();

    const autocompleteChanged = useCallback(() => {
        const place = autocomplete.current.getPlace();

        if (place) {
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
        }
    }, [setFieldValue]);

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
    }, [autocompleteChanged]);

    return (
        <TextInput
            ref={ref}
            name="search"
            label="Search"
            help="Start typing to search for a location."
        />
    );
    // return <div ref={ref} id="map" className="h-64" />;
};

const Map = ({ google_maps_api_key }: { google_maps_api_key: string }) => {
    const {
        values,
    }: {
        values: {
            place_id: string;
        };
    } = useFormikContext();

    if (values.place_id) {
        return (
            <>
                <iframe
                    title="Map view"
                    className="mb-3"
                    width="100%"
                    height="450"
                    style={{ border: 0 }}
                    loading="lazy"
                    allowFullScreen
                    referrerPolicy="no-referrer-when-downgrade"
                    src={`https://www.google.com/maps/embed/v1/place?key=${encodeURIComponent(
                        google_maps_api_key
                    )}&q=place_id:${encodeURIComponent(values.place_id)}`}
                ></iframe>
            </>
        );
    }
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
                        route: "venues.new",
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={"Create Venue"}
                    subtitle={`Create a new venue`}
                ></MainHeader>
            </Container>

            <Container noMargin>
                <Form
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A venue name is required."),
                    })}
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
                    hideDefaultButtons
                    hideErrors
                >
                    <Card footer={<SubmissionButtons />}>
                        <div className="grid grid-cols-12 gap-4">
                            <div className="col-span-full md:col-span-6">
                                <FlashAlert />
                                <RenderServerErrors />

                                <Wrapper
                                    apiKey={props.google_maps_api_key}
                                    render={render}
                                    libraries={["places"]}
                                >
                                    <MapComponent />
                                </Wrapper>
                                <TextInput name="name" label="Venue name" />

                                <TextInput
                                    name="formatted_address"
                                    label="Formatted address"
                                    readOnly
                                    help="This field is read only."
                                />

                                <p className="text-sm mb-3">
                                    Please check you&apos;re happy that this
                                    location is correct before you continue. If
                                    you aren&apos;t, please search again.
                                </p>

                                <p className="text-sm">
                                    We&apos;ll populate and periodically update
                                    information for this venue with data from
                                    Google Maps
                                </p>
                            </div>
                            <div className="col-span-full md:col-span-6">
                                <Map
                                    google_maps_api_key={
                                        props.google_maps_api_key
                                    }
                                />
                            </div>
                        </div>
                    </Card>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
