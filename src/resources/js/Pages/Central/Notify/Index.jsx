import React from "react";
import Container from "@/Components/Container";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { Head } from "@inertiajs/inertia-react";
import Badge from "@/Components/Badge";
import FileList from "@/Components/FileList";
import { DefinitionList } from "@/Components/DefinitionList";
import { format } from "date-fns";
import BaseLink from "@/Components/BaseLink";

const ItemContent = (props) => {
    const logo = props.logo_path
        ? `${props.logo_path}icon-72x72.png`
        : "/img/corporate/scds.svg";

    const items = [
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
                <div className="">
                    <BaseLink
                        href={route("central.clubs.redirect", props.tenant.id)}
                    >
                        <Badge>{props.tenant.name}</Badge>
                    </BaseLink>
                </div>
            </div>
            <DefinitionList items={items} verticalPadding={2} />
        </>
    );
};

const Index = (props) => {
    return (
        <Container noMargin>
            <Head title="Notify History" />
            <PlainCollection
                {...props.emails}
                itemRenderer={ItemContent}
                route="central.notify.show"
                routeIdName="id"
            />
        </Container>
    );
};

Index.layout = (page) => (
    <CentralMainLayout title="Notify" subtitle="Monitor notify history">
        {page}
    </CentralMainLayout>
);

export default Index;
