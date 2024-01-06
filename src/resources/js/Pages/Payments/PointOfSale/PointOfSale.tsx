import React from "react";
import Head from "@/Components/Head";
import Card from "@/Components/Card";
import Button from "@/Components/Button";

type POSButtonProps = {
    children: React.ReactNode;
};

type POSButtonGroupProps = {
    children: React.ReactNode;
};

const POSButton = (props: POSButtonProps) => {
    return (
        <Button
            variant="secondary"
            className="h-20 flex items-center overflow-hidden"
        >
            <div>{props.children}</div>
        </Button>
    );
};

const POSButtonGroup = (props: POSButtonGroupProps) => {
    return (
        <div className="grid gap-1 grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            {props.children}
        </div>
    );
};

type Props = {};

const PointOfSale = (props: Props) => {
    return (
        <>
            <Head
                title="SCDS Point Of Sale"
                breadcrumbs={[
                    {
                        name: "Point Of Sale",
                        route: "pos.index",
                    },
                ]}
            />

            {/*<Container fluid noMargin>*/}
            {/* 3 column wrapper */}
            <div className="lg:flex h-screen">
                {/* Left sidebar & main wrapper */}
                <div className="flex-1 h-full overflow-y-auto overflow-x-visible scroll-auto">
                    <div className="p-4">
                        <div className="grid gap-4">
                            <POSButtonGroup>
                                <POSButton>Adult</POSButton>
                                <POSButton>Child</POSButton>
                                <POSButton>Teen</POSButton>
                                <POSButton>Concession (65+)</POSButton>
                                <POSButton>Honorary Member</POSButton>
                                <POSButton>Free Guest</POSButton>
                            </POSButtonGroup>
                            <POSButtonGroup>
                                <POSButton>Programme</POSButton>
                                <POSButton>Start Sheet</POSButton>
                            </POSButtonGroup>
                            <POSButtonGroup>
                                <POSButton>Raffle Ticket</POSButton>
                            </POSButtonGroup>
                            <POSButtonGroup>
                                <POSButton>50p Sweets Mix</POSButton>
                                <POSButton>£1 Sweets Mix</POSButton>
                                <POSButton>£2 Sweets Mix</POSButton>
                                <POSButton>Cake</POSButton>
                            </POSButtonGroup>
                        </div>
                    </div>
                </div>

                <div className="shrink-0 border-t border-gray-200 md:w-96 md:border-l md:border-t-0 h-full scroll-auto overflow-auto">
                    <div className="p-4 grid gap-4">
                        <Card title="Current transaction">
                            <div className="grid gap-2">
                                <Button variant="primary">Charge</Button>
                                <Button variant="secondary">
                                    Cancel charge/Reset reader
                                </Button>
                                <Button variant="danger">Clear cart</Button>
                            </div>
                        </Card>

                        <Card title="Basket"></Card>

                        <Card title="Point of sale options">
                            <div className="grid gap-2">
                                <Button variant="secondary">Exit</Button>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
            {/*</Container>*/}
        </>
    );
};

// PointOfSale.layout = (page) => (
//     <MainLayout hideHeader>
//         <Container fluid noMargin>
//             {page}
//         </Container>
//     </MainLayout>
// );

export default PointOfSale;
