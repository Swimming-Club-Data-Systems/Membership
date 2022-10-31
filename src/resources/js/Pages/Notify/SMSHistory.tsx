import React, { ReactNode } from "react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/inertia-react";
import { format } from "date-fns";

type Message = {
    author: {
        Forename: string;
        Surname: string;
        UserID: number;
    };
    created_at: string;
    message: string;
};

const ItemContent: React.FC<Message> = (props) => {
    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <div>
                        <p className="text-sm">
                            {props.author.Forename} {props.author.Surname} at{" "}
                            {format(
                                Date.parse(props.created_at),
                                "HH:mm, do MMMM yyyy"
                            )}
                        </p>
                        <p className="text-sm">{props.message}</p>
                    </div>
                </div>
            </div>
        </>
    );
};

type Props = {
    messages: {
        data: Message[];
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
                itemRenderer={ItemContent}
                route="notify.sms.history"
                routeIdName="ID"
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
