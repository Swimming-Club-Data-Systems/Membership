import React from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import Collection, { LaravelPaginatorProps } from "@/Components/Collection";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader.jsx";
import FlashAlert from "@/Components/FlashAlert.jsx";
import ButtonLink from "@/Components/ButtonLink.js";

type Member = {
    id: number;
    MForename: string;
    MSurname: string;
    ASANumber: string;
    squads: {
        SquadName: string;
    }[];
};

const ItemContent = (props: Member) => {
    const squadNames = props.squads.map((squad) => squad.SquadName);

    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    {/*<div className="">*/}
                    {/*    <img*/}
                    {/*        className="h-8 w-8 rounded-full"*/}
                    {/*        src={props.gravatar_url}*/}
                    {/*        alt=""*/}
                    {/*    />*/}
                    {/*</div>*/}
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="truncate text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                            {props.MForename} {props.MSurname}
                        </div>
                        <div className="truncate text-sm text-gray-700 group-hover:text-gray-800">
                            {props.ASANumber} -{" "}
                            {squadNames.length > 0
                                ? squadNames.join(", ")
                                : "No Squads"}
                        </div>
                    </div>
                </div>
                {/*<div className="ml-2 flex flex-shrink-0">*/}
                {/*    <span className="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">*/}
                {/*        Active*/}
                {/*    </span>*/}
                {/*</div>*/}
            </div>
        </>
    );
};

type Props = {
    members: LaravelPaginatorProps<Member>;
    can_create: boolean;
};

const Index = (props: Props) => {
    return (
        <>
            <Head title="Members" />

            <Container>
                <MainHeader
                    title="Members"
                    subtitle="Member list"
                    buttons={
                        props.can_create && (
                            <ButtonLink href={route("members.new")}>
                                New
                            </ButtonLink>
                        )
                    }
                    breadcrumbs={[{ name: "Members", route: "members.index" }]}
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <Collection
                    searchable
                    {...props.members}
                    itemRenderer={ItemContent}
                    route="members.show"
                    routeIdName="MemberID"
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
