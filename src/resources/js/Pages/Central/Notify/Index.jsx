import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/react";
import EmailListItemContent from "@/Components/Notify/EmailListItemContent";

const Index = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify Email History" />
            <PlainCollection
                {...props.emails}
                itemRenderer={(item) => <EmailListItemContent {...item} />}
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
