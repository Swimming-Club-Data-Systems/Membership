import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import BasicList from "@/Components/BasicList";
import Link from "@/Components/Link";
import Badge from "@/Components/Badge";
import Alert from "@/Components/Alert";
import { Price } from "@/Pages/Prices/New";
import ButtonLink from "@/Components/ButtonLink";

type Props = {
    id: string;
    name: string;
    unit_label: string;
    prices: Price[];
};

const Show = (props: Props) => {
    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Products", route: "products.index" },
                    {
                        name: props.name,
                        route: "products.show",
                        routeParams: {
                            product: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle="Product"
                    buttons={
                        <ButtonLink
                            href={route("products.prices.new", {
                                product: props.id,
                            })}
                        >
                            New price
                        </ButtonLink>
                    }
                />
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <FlashAlert />

                    <Card
                        title="Prices"
                        subtitle={`${props.name} has ${
                            props.prices.length
                        } price${props.prices.length === 1 ? "" : "s"}`}
                    >
                        {props.prices.length > 0 && (
                            <BasicList
                                items={props.prices.map((price) => {
                                    return {
                                        id: price.id,
                                        content: (
                                            <>
                                                <div
                                                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-2 text-sm"
                                                    key={price.id}
                                                >
                                                    <div className="text-gray-900">
                                                        <div className="mb-1">
                                                            <Link
                                                                href={route(
                                                                    "products.prices.show",
                                                                    {
                                                                        product:
                                                                            props.id,
                                                                        price: price.id,
                                                                    },
                                                                )}
                                                            >
                                                                {price.nickname}
                                                            </Link>
                                                        </div>
                                                        <div>
                                                            {
                                                                price.formatted_unit_amount
                                                            }
                                                            /{props.unit_label}
                                                        </div>
                                                    </div>
                                                    <div className="block">
                                                        <div className="flex gap-1">
                                                            {!price.active && (
                                                                <Badge colour="red">
                                                                    Inactive
                                                                </Badge>
                                                            )}
                                                            {price.active && (
                                                                <Badge colour="green">
                                                                    Active
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </>
                                        ),
                                    };
                                })}
                            />
                        )}
                        {props.prices.length === 0 && (
                            <Alert
                                title="No prices to display"
                                variant="warning"
                            >
                                This squad currently has no members.
                            </Alert>
                        )}
                    </Card>
                </div>
            </Container>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
