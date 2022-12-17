import React, { ReactNode } from "react";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/inertia-react";
import { format, parseISO } from "date-fns";

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
                        <p className="text-sm font-medium">
                            {props.author.Forename} {props.author.Surname} at{" "}
                            {format(
                                parseISO(props.created_at),
                                "HH:mm, do MMMM yyyy"
                            )}
                        </p>
                        <div className="prose prose-sm">
                            <p>{props.message}</p>
                        </div>
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
