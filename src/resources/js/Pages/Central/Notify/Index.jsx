import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/react";
import Badge from "@/Components/Badge";
import FileList from "@/Components/FileList";
import { DefinitionList } from "@/Components/DefinitionList";
import { format } from "date-fns";
import BaseLink from "@/Components/BaseLink";
import EmailListItemContent from "@/Components/Notify/EmailListItemContent";

const Index = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify Email History" />
            <PlainCollection
                {...props.emails}
                itemRenderer={EmailListItemContent}
                route="central.notify.show"
                routeIdName="id"
            />
        </Container>
    );
};

Index.layout = (page) => (
    <CentralMainLayout title="Email History" subtitle="Monitor notify history">
        {page}
    </CentralMainLayout>
);

export default Index;
