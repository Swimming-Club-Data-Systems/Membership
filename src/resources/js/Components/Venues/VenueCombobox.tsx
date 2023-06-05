import React from "react";
import Combobox from "@/Components/Form/Combobox";

export const VenueCombobox = (props: { name: string }) => {
    return (
        <Combobox
            endpoint={route("venues.combobox")}
            name={props.name}
            label="Venue"
            help="Start typing to find a venue"
        />
    );
};
