import React, { ReactNode } from "react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/react";
import SMSListItemContent, {
    SMSListItemContentProps,
} from "@/Components/Notify/SMSListItemContent";
import { LaravelPaginatorProps } from "@/Components/Collection.tsx";

type Props = {
    messages: LaravelPaginatorProps<SMSListItemContentProps>;
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
