import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import Collection from "@/Components/Collection";
import { Layout } from "@/Common/Layout";
import {
    BalanceTopUpContent,
    BalanceTopUpIndexProps,
} from "@/Pages/Payments/BalanceTopUps/Index";
import ButtonLink from "@/Components/ButtonLink";
import MainHeader from "@/Layouts/Components/MainHeader";
import FlashAlert from "@/Components/FlashAlert";

const Index: Layout<BalanceTopUpIndexProps> = (
    props: BalanceTopUpIndexProps
) => {
    const title = "Balance Top Ups";
    const subtitle = `View balance top ups for ${props.user.name}`;

    return (
        <>
            <Head
                title={title}
                breadcrumbs={[
                    { name: "Users", route: "users.index" },
                    {
                        name: props.user.name,
                        route: "users.show",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: "Balance Top Ups",
                        route: "users.top_up.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
                subtitle={subtitle}
            />

            <Container noMargin>
                <MainHeader
                    title={title}
                    subtitle={subtitle}
                    buttons={
                        <ButtonLink
                            href={route("users.top_up.new", props.user.id)}
                        >
                            New
                        </ButtonLink>
                    }
                ></MainHeader>

                <FlashAlert className="mb-3" />

                <Collection
                    {...props.balance_top_ups}
                    route="users.top_up.show"
                    routeParams={[props.user.id]}
                    itemRenderer={BalanceTopUpContent}
                />
            </Container>
        </>
    );
};

Index.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Index;
