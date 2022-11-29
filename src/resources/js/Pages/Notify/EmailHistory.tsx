import React, { ReactNode } from "react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/inertia-react";
import EmailListItemContent, {
    EmailListItemContentProps,
} from "@/Components/Notify/EmailListItemContent";

type Props = {
    emails: {
        data: EmailListItemContentProps[];
    };
};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const EmailHistory: Layout<Props> = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify Email History" />
            <PlainCollection
                {...props.emails}
                itemRenderer={EmailListItemContent}
                route="notify.email.history"
                routeIdName="id"
            />
        </Container>
    );
};

EmailHistory.layout = (page) => (
    <MainLayout
        title="Notify Email History"
        subtitle="View previous email messages"
    >
        {page}
    </MainLayout>
);

export default EmailHistory;
