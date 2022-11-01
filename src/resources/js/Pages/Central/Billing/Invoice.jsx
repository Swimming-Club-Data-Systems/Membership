import React from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Card from "@/Components/Card";
import Container from "@/Components/Container";
import Link from "@/Components/Link";

const Invoice = (props) => {
    return (
        <>
            <Head title={`Invoice ${props.invoice.number}`} />

            <Container>
                <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                    <Card
                        title="Invoice Details"
                        subtitle="Manage your payment methods, payments and
                                subscriptions."
                    >
                        <div>
                            <p className="text-sm mb-3">
                                <Link
                                    external
                                    href={props.invoice.hosted_invoice_url}
                                >
                                    View on Stripe
                                </Link>
                            </p>

                            <p className="text-sm">
                                <Link external href={props.invoice.invoice_pdf}>
                                    Download invoice PDF
                                </Link>
                            </p>
                        </div>
                    </Card>

                    <Card
                        title="Invoice Items"
                        subtitle="Manage your payment methods, payments and
                                subscriptions."
                    >
                        <div className="mt-8 flex flex-col">
                            <div className="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div className="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                                    <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                        <table className="min-w-full divide-y divide-gray-300">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th
                                                        scope="col"
                                                        className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                    >
                                                        Description
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                                    >
                                                        Quantity
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                    >
                                                        Amount before tax
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                    >
                                                        Amount with tax
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                    >
                                                        Currency
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 bg-white">
                                                {props.lines.map((line) => (
                                                    <tr key={"X"}>
                                                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                            {line.description}
                                                        </td>
                                                        <td className="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                            {line.quantity}
                                                        </td>
                                                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                            {
                                                                line.amount_excluding_tax
                                                            }
                                                        </td>
                                                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                            {line.amount}
                                                        </td>
                                                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                            {line.currency}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </Container>
        </>
    );
};

Invoice.layout = (page) => (
    <CentralMainLayout
        title={`Invoice ${page.props.invoice.number}`}
        subtitle={`${page.props.invoice.customer_name} - Invoice ${page.props.invoice.status}`}
    >
        {page}
    </CentralMainLayout>
);

export default Invoice;
