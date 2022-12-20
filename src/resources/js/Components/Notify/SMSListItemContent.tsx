import React, { ReactNode } from "react";
import Badge from "@/Components/Badge";
import BaseLink from "@/Components/BaseLink";
import {
    DefinitionList,
    DefinitionListItemProps,
} from "@/Components/DefinitionList";
import { usePage } from "@inertiajs/inertia-react";
import { formatDateTime } from "@/Utils/date-utils";

export type SMSListItemContentProps = {
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
    created_at: string;
    message: string;
    tenant: {
        name: string;
        id: number;
    };
};

const SMSListItemContent: React.FC<SMSListItemContentProps> = (props) => {
    const items: DefinitionListItemProps[] = [
        {
            key: "sent_by",
            term: "Sent by",
            definition: props.author
                ? props.author.first_name + " " + props.author.last_name
                : "Unknown author",
        },
    ];

    items.push({
        key: "message",
        term: "Message",
        definition: <div className="prose prose-sm">{props.message}</div>,
    });

    return (
        <>
            <div className="flex justify-between items-center">
                <div className="flex-grow">
                    <h3 className="block text-lg font-medium leading-6 text-gray-900">
                        SMS Message
                    </h3>
                    <p className="block mt-1 max-w-2xl text-sm text-gray-500">
                        {formatDateTime(props.created_at)}
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

export default SMSListItemContent;
