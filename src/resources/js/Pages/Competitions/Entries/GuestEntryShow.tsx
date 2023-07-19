import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import * as yup from "yup";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";
import { FieldArray, useField } from "formik";
import Button from "@/Components/Button";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import { formatISO } from "date-fns";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import FlashAlert from "@/Components/FlashAlert";
import BasicList from "@/Components/BasicList";
import { formatDate } from "@/Utils/date-utils";
import { DefinitionList } from "@/Components/DefinitionList";
import Link from "@/Components/Link";

export type Props = {
    google_maps_api_key: string;
    competition: {
        name: string;
        id: number;
    };
    id: string;
    first_name: string;
    last_name: string;
    email: string;
    entrants: {
        id: string;
        first_name: string;
        last_name: string;
        date_of_birth: string;
        sex: string;
    }[];
    tenant: {
        name: string;
    };
};

type FieldArrayItemsProps = {
    name: string;
    render: (index: number, length: number) => ReactNode;
};

const GuestEntryShow: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Guest Entry"
                breadcrumbs={[
                    { name: "Competitions", route: "competitions.index" },
                    {
                        name: props.competition.name,
                        route: "competitions.show",
                        routeParams: {
                            competition: props.competition.id,
                        },
                    },
                    { name: "Guest Entry", route: "competitions.index" },
                ]}
            />

            <Container>
                <MainHeader
                    title={"Manage your entries"}
                    subtitle={`Hi ${props.first_name}, you can enter, update details and pay from here.`}
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <Card title="Your details">
                        <DefinitionList
                            verticalPadding={2}
                            items={[
                                {
                                    key: "name",
                                    term: "Name",
                                    definition: `${props.first_name} ${props.last_name}`,
                                },
                                {
                                    key: "email",
                                    term: "Email address",
                                    definition: props.email,
                                },
                            ]}
                        />
                    </Card>

                    <Card title="Entrants">
                        <BasicList
                            items={props.entrants.map((entrant) => {
                                return {
                                    id: entrant.id,
                                    content: (
                                        <div className="flex items-center justify-between text-sm">
                                            <div>
                                                <div>
                                                    {entrant.first_name}{" "}
                                                    {entrant.last_name}{" "}
                                                </div>
                                                <div>
                                                    {formatDate(
                                                        entrant.date_of_birth
                                                    )}
                                                </div>
                                            </div>
                                            <div>
                                                <Link
                                                    href={route(
                                                        "competitions.enter_as_guest.edit_entry",
                                                        {
                                                            competition:
                                                                props
                                                                    .competition
                                                                    .id,
                                                            header: props.id,
                                                            entrant: entrant.id,
                                                        }
                                                    )}
                                                >
                                                    Select swims{" "}
                                                    <span aria-hidden="true">
                                                        {" "}
                                                        &rarr;
                                                    </span>
                                                </Link>
                                            </div>
                                        </div>
                                    ),
                                };
                            })}
                        ></BasicList>
                    </Card>

                    <Card title="Payment">
                        <div className="text-sm">
                            You can&apos;t make payment right now.
                        </div>
                    </Card>
                </div>
            </Container>
        </>
    );
};

GuestEntryShow.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default GuestEntryShow;
