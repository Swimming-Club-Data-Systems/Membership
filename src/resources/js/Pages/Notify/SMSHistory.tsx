import React, { ReactNode } from "react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/inertia-react";
import SMSListItemContent, {
    SMSListItemContentProps,
} from "@/Components/Notify/SMSListItemContent";

type Props = {
    messages: {
        data: SMSListItemContentProps[];
    };
};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const SMSHistory: Layout<Props> = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify SMS History" />
            <PlainCollection
                {...props.messages}
                itemRenderer={SMSListItemContent}
                route="notify.sms.history"
                routeIdName="id"
            />
        </Container>
    );
};

SMSHistory.layout = (page) => (
    <MainLayout
        title="Notify SMS History"
        subtitle="View previous SMS messages"
    >
        {page}
    </MainLayout>
);

export default SMSHistory;
