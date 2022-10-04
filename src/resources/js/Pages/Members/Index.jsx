import React from "react";
import { Head } from "@inertiajs/inertia-react";
import Container from "@/Components/Container";
import Collection from "@/Components/Collection";
import MainLayout from "@/Layouts/MainLayout";

const ItemContent = (props) => {
    const squadNames = props.squads.map((squad) => squad.SquadName);

    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    {/*<div className="">*/}
                    {/*    <img*/}
                    {/*        className="h-8 w-8 rounded-full"*/}
                    {/*        src={props.gravitar_url}*/}
                    {/*        alt=""*/}
                    {/*    />*/}
                    {/*</div>*/}
                    <div>
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
                <div className="ml-2 flex flex-shrink-0">
                    <span className="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                        Active
                    </span>
                </div>
            </div>
        </>
    );
};

const Index = (props) => {
    return (
        <>
            <Head title="Members" />

            <Container noMargin>
                <Collection
                    {...props.members}
                    itemRenderer={ItemContent}
                    route="members.show"
                    routeIdName="MemberID"
                />
            </Container>
        </>
    );
};

const crumbs = [{ href: "/members", name: "Members" }];

Index.layout = (page) => (
    <MainLayout title="Members" subtitle="Member list">
        {page}
    </MainLayout>
);

export default Index;
