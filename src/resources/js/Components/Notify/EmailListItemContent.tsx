import React, { ReactNode } from "react";
import Badge from "@/Components/Badge";
import FileList, { FileProps } from "@/Components/FileList";
import { format } from "date-fns";
import BaseLink from "@/Components/BaseLink";
import {
    DefinitionList,
    DefinitionListItemProps,
} from "@/Components/DefinitionList";
import { usePage } from "@inertiajs/inertia-react";

export type EmailListItemContentProps = {
    items: {
        key: string | number;
        term: ReactNode;
        definition: ReactNode;
    }[];
    author: {
        first_name: string;
        last_name: string;
        id: number;
    };
    sent_as?: {
        email: string;
        name: string;
    };
    reply_to?: {
        email: string;
        name: string;
    };
    sent_to: {
        id: string | number;
        name: string;
        type: string;
    }[];
    attachments?: FileProps[];
    subject: string;
    date: string;
    message: string;
    tenant: {
        name: string;
        id: number;
    };
};

const EmailListItemContent: React.FC<EmailListItemContentProps> = (props) => {
    const items: DefinitionListItemProps[] = [
        {
            key: "sent_by",
            term: "Sent by",
            definition: props.author.first_name + " " + props.author.last_name,
        },
    ];

    if (props.sent_as) {
        items.push({
            key: "sent_as",
            term: "Sent as",
            definition: props.sent_as.name,
        });
    }

    items.push({
        key: "sent_to",
        term: "Sent to",
        definition: props.sent_to.map((item) => (
            <>
                <Badge key={item.id}>{item.name}</Badge>{" "}
            </>
        )),
    });

    if (props.attachments.length > 0) {
        items.push({
            key: "attachments",
            term: "Attachments",
            definition: <FileList items={props.attachments} />,
        });
    }

    if (props.reply_to) {
        items.push({
            key: "reply_to",
            term: "Custom reply to",
            definition: `${props.reply_to.name} <${props.reply_to.email}>`,
        });
    }

    items.push({
        key: "message",
        term: "Message",
        definition: (
            <div
                className="prose prose-sm"
                dangerouslySetInnerHTML={{
                    __html: props.message,
                }}
            />
        ),
    });

    return (
        <>
            <div className="flex justify-between items-center">
                <div className="flex-grow">
                    <h3 className="block text-lg font-medium leading-6 text-gray-900">
                        {props.subject}
                    </h3>
                    <p className="block mt-1 max-w-2xl text-sm text-gray-500">
                        {format(Date.parse(props.date), "HH:mm, do MMMM yyyy")}
                    </p>
                </div>
                {usePage().props.central && (
                    <div className="">
                        <BaseLink
                            href={route(
                                "central.clubs.redirect",
                                props.tenant.id
                            )}
                        >
                            <Badge>{props.tenant.name}</Badge>
                        </BaseLink>
                    </div>
                )}
            </div>
            <DefinitionList items={items} verticalPadding={2} />
        </>
    );
};

export default EmailListItemContent;
