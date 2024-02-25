import React, { ReactNode, useMemo } from "react";
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
import { formatISO } from "date-fns";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import FlashAlert from "@/Components/FlashAlert";
import getCustomInitialValues from "@/Utils/Form/getCustomInitialValues";
import generateFields from "@/Utils/Form/generateFields";
import { Field } from "@/Utils/Form/Field";
import generateYupFields from "@/Utils/Form/generateYupFields";
import Link from "@/Components/Link";
import DateNumeralInput from "@/Components/Form/DateNumeralInput";
import BasicList from "@/Components/BasicList";
import { formatDate } from "@/Utils/date-utils";
import EmptyState from "@/Components/EmptyState";

export type Props = {
    competition: {
        name: string;
        id: number;
    };
    user?: {
        first_name: string;
        last_name: string;
        email: string;
    };
    tenant: {
        name: string;
    };
    members: {
        id: number;
        name: string;
        age: number;
        date_of_birth: string;
        paid: boolean;
        formatted_amount: string;
    }[];
};

const NewMemberEntrySelect: Layout<Props> = (props: Props) => {
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
                    { name: "Select a member", route: "competitions.index" },
                ]}
            />

            <Container>
                <MainHeader
                    title={"Select a member"}
                    subtitle={`Choose who to enter`}
                ></MainHeader>
            </Container>

            <Container noMargin>
                <div className="grid gap-6">
                    {props.members.length > 0 && (
                        <>
                            <Card title="Members">
                                <BasicList
                                    items={props.members.map((member) => {
                                        return {
                                            id: member.id,
                                            content: (
                                                <div className="flex items-center justify-between text-sm">
                                                    <div>
                                                        <div>
                                                            {member.name} (
                                                            {member.age})
                                                        </div>
                                                        <div>
                                                            {formatDate(
                                                                member.date_of_birth,
                                                            )}{" "}
                                                            -{" "}
                                                            {
                                                                member.formatted_amount
                                                            }
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <Link
                                                            href={route(
                                                                "competitions.enter.edit_entry",
                                                                {
                                                                    competition:
                                                                        props
                                                                            .competition
                                                                            .id,
                                                                    member: member.id,
                                                                },
                                                            )}
                                                        >
                                                            {member.paid
                                                                ? "View swims"
                                                                : "Select swims"}{" "}
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

                            <Card title={`${props.tenant.name} and your data`}>
                                <div className="prose prose-sm">
                                    <p>
                                        By continuing, you consent to the
                                        storage and use of your personal data by{" "}
                                        {props.tenant.name} for the purposes of
                                        processing your entry.
                                    </p>

                                    <p>
                                        By proceeding, you also confirm that you
                                        accept the{" "}
                                        <Link
                                            href="/privacy"
                                            target="_blank"
                                            external
                                        >
                                            {props.tenant.name} terms and
                                            conditions relating to use of their
                                            services, competition entries and
                                            more
                                        </Link>
                                        .
                                    </p>

                                    <p>
                                        Use of this software is subject to the
                                        Swimming Club Data Systems (SCDS) terms
                                        and conditions, license agreements and
                                        responsible use policies, details of
                                        which can be found on the SCDS website.
                                        SCDS reserves the right to make changes
                                        to these terms and policies at any time.
                                    </p>
                                </div>
                            </Card>
                        </>
                    )}

                    {props.members.length === 0 && (
                        <EmptyState title="No members">
                            <p>
                                There are no members connected to your account.
                            </p>
                        </EmptyState>
                    )}
                </div>
            </Container>
        </>
    );
};

NewMemberEntrySelect.layout = (page) => (
    <MainLayout hideHeader>{page}</MainLayout>
);

export default NewMemberEntrySelect;
