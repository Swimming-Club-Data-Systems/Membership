import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/react";
import SMSListItemContent from "@/Components/Notify/SMSListItemContent";

const Index = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify SMS History" />
            <PlainCollection
                {...props.messages}
                itemRenderer={(item) => <SMSListItemContent {...item} />}
                route="central.notify.show"
                routeIdName="id"
            />
        </Container>
    );
};

Index.layout = (page) => (
    <CentralMainLayout title="SMS History" subtitle="Monitor notify history">
        {page}
    </CentralMainLayout>
);

export default Index;
