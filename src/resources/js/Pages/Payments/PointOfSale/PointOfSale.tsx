import React, {
    useCallback,
    useEffect,
    useReducer,
    useRef,
    useState,
} from "react";
import Head from "@/Components/Head";
import Card from "@/Components/Card";
import Button, { Props as ButtonProps } from "@/Components/Button";
import ButtonLink from "@/Components/ButtonLink";
import BasicList from "@/Components/BasicList";
import {
    MinusCircleIcon,
    TrashIcon,
    CreditCardIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from "@heroicons/react/24/solid";
import axios from "@/Utils/axios";
import BigNumber from "bignumber.js";
import Modal from "@/Components/Modal";
// import Echo from "laravel-echo";

type POSButtonProps = {
    onClick: ButtonProps["onClick"];
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
            onClick={props.onClick}
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

type Props = {
    id: string;
    item_groups: {
        id: string;
        name: string;
        items: {
            id: string;
            label: string;
            unit_amount: number;
        }[];
    }[];
    reader_id: string;
};

type Item = {
    id: string;
    label: string;
    quantity: number;
    unit_amount: number;
};

type ReducerAction = {
    type: "addItem" | "removeItem" | "removeAllOfItem" | "reset";
    item?: Item;
};

type State = {
    items: Item[];
    iteration: number;
};

const reducer = (state: State, action: ReducerAction): State => {
    const newArray: Item[] = [];

    // Create new state array
    state.items.forEach((item: Item) => {
        newArray.push({
            id: item.id,
            label: item.label,
            quantity: item.quantity,
            unit_amount: item.unit_amount,
        });
    });

    const findIndex = (index: string) =>
        newArray.findIndex((item) => {
            return item.id === index;
        });

    if (action.type === "addItem") {
        // If item already in list then increment the number, else add
        const index = findIndex(action.item.id);

        if (index >= 0) {
            newArray[index].quantity++;
        } else {
            newArray.push({
                id: action.item.id,
                label: action.item.label,
                quantity: 1,
                unit_amount: action.item.unit_amount,
            });
        }

        return { items: newArray, iteration: state.iteration + 1 };
    } else if (action.type === "removeItem") {
        // Decrement or remove
        const index = findIndex(action.item.id);
        if (index >= 0) {
            newArray[index].quantity--;

            if (newArray[index].quantity === 0) {
                newArray.splice(index, 1);
            }
        }

        return { items: newArray, iteration: state.iteration + 1 };
    } else if (action.type === "reset") {
        return { items: [], iteration: state.iteration + 1 };
    } else if (action.type === "removeAllOfItem") {
        const index = findIndex(action.item.id);
        if (index >= 0) {
            newArray.splice(index, 1);
        }

        return { items: newArray, iteration: state.iteration + 1 };
    } else {
        throw Error("Unknown action.");
    }
};

const PointOfSale = (props: Props) => {
    const [state, dispatchItems] = useReducer(reducer, {
        items: [],
        iteration: 0,
    });
    const [count, setCount] = useState(0);
    const prevIteration = useRef<number>(0);
    const [readerStatus, setReaderStatus] = useState<string>("OK");

    const [showWaiting, setShowWaiting] = useState<boolean>(false);
    const [showSuccess, setShowSuccess] = useState<boolean>(false);
    const [showError, setShowError] = useState<boolean>(false);

    useEffect(() => {
        try {
            window.Echo.private(`reader.${props.reader_id}`)
                .listen("Tenant\\PointOfSale\\ReaderStatusUpdated", (e) => {
                    console.log(e);
                })
                .listen("Tenant\\PointOfSale\\ReaderActionFailed", (e) => {
                    setReaderStatus(e.data.failure_message);
                    setShowWaiting(false);
                    setShowError(true);
                    console.log(e);
                })
                .listen("Tenant\\PointOfSale\\ReaderActionSucceeded", (e) => {
                    setReaderStatus("OK");
                    setShowWaiting(false);
                    setShowSuccess(true);
                    console.log(e);
                });
        } catch (error) {
            console.log(error);
        }
    }, [props.reader_id]);

    const clearPayment = useCallback(() => {
        try {
            axios.post(route("pos.clear-payment"));
            setShowError(false);
        } catch (error) {}
    }, []);

    const clearReader = useCallback(() => {
        try {
            axios.post(
                route("pos.clear-reader", {
                    reader: props.reader_id,
                }),
            );
            setShowError(false);
        } catch (error) {}
    }, [props.reader_id]);

    const charge = () => {
        try {
            axios.post(
                route("pos.charge", {
                    reader: props.reader_id,
                }),
                { items: state.items },
            );
            setShowWaiting(true);
            setShowError(false);
        } catch (error) {
            setShowWaiting(false);
        }
    };

    const cancelCharge = () => {
        clearReader();
        setShowWaiting(false);
    };

    const clearAfterSuccess = () => {
        dispatchItems({
            type: "reset",
        });
        setShowSuccess(false);
        clearPayment();
    };

    // useEffect(() => {
    //     // As items is updated, set the reader display
    //     try {
    //         const uninterceptedAxiosInstance = axios.create();
    //         uninterceptedAxiosInstance.post(
    //             route("pos.set-reader-display", {
    //                 reader: props.reader_id,
    //             }),
    //             { items: state.items },
    //         );
    //     } catch (error) {}
    // }, [state.iteration]);

    // useEffect(() => {
    //     const timer = setTimeout(() => {
    //         if (prevIteration.current !== state.iteration) {
    //             try {
    //                 console.log("Running");
    //                 const uninterceptedAxiosInstance = axios.create();
    //                 uninterceptedAxiosInstance.post(
    //                     route("pos.set-reader-display", {
    //                         reader: props.reader_id,
    //                     }),
    //                     { items: state.items },
    //                 );
    //             } catch (error) {}
    //         }
    //         setCount(count + 1);
    //         prevIteration.current = state.iteration;
    //     }, 2000);
    //     return () => clearTimeout(timer);
    // }, [props.reader_id, count]);

    const showCartOnReader = useCallback(() => {
        try {
            const uninterceptedAxiosInstance = axios.create();
            uninterceptedAxiosInstance.post(
                route("pos.set-reader-display", {
                    reader: props.reader_id,
                }),
                { items: state.items },
            );
        } catch (error) {}
    }, [state.iteration, props.reader_id]);

    const total = state.items.reduce(
        (acc, next) => acc + next.unit_amount * next.quantity,
        0,
    );

    return (
        <>
            <Head
                title="SCDS Point Of Sale"
                breadcrumbs={[
                    {
                        name: "Point Of Sale",
                        route: "pos.show",
                        routeParams: props.id,
                    },
                ]}
            />

            <div className="lg:flex h-screen">
                <div className="flex-1 h-full overflow-y-auto overflow-x-visible scroll-auto">
                    <div className="p-4">
                        <div className="grid gap-4">
                            {props.item_groups.map((group) => {
                                return (
                                    <POSButtonGroup key={group.id}>
                                        {group.items.map((item) => {
                                            return (
                                                <POSButton
                                                    key={item.id}
                                                    onClick={() => {
                                                        dispatchItems({
                                                            type: "addItem",
                                                            item: {
                                                                id: item.id,
                                                                label: item.label,
                                                                quantity: 0,
                                                                unit_amount:
                                                                    item.unit_amount,
                                                            },
                                                        });
                                                    }}
                                                >
                                                    {item.label}
                                                </POSButton>
                                            );
                                        })}
                                    </POSButtonGroup>
                                );
                            })}
                        </div>
                    </div>
                </div>

                <div className="shrink-0 border-t border-gray-200 md:w-96 md:border-l md:border-t-0 h-full scroll-auto overflow-auto">
                    <div className="p-4 grid gap-4">
                        <Card title="Current transaction">
                            <div className="grid gap-2">
                                <Button
                                    variant="primary"
                                    onClick={charge}
                                    disabled={total < 1}
                                >
                                    Charge
                                </Button>
                                {/*<Button*/}
                                {/*    variant="secondary"*/}
                                {/*    onClick={showCartOnReader}*/}
                                {/*>*/}
                                {/*    Show basket on reader*/}
                                {/*</Button>*/}
                                <Button
                                    variant="secondary"
                                    onClick={() => {
                                        clearReader();
                                    }}
                                >
                                    Cancel charge/Reset reader
                                </Button>
                                <Button
                                    variant="danger"
                                    onClick={() => {
                                        dispatchItems({
                                            type: "reset",
                                        });
                                        clearReader();
                                    }}
                                >
                                    Clear cart
                                </Button>
                                <Button
                                    variant="danger"
                                    onClick={() => {
                                        clearPayment();
                                    }}
                                >
                                    Clear payment in session
                                </Button>
                            </div>
                        </Card>

                        <Card
                            title="Basket"
                            footer={
                                <div className="text-sm">
                                    Total{" "}
                                    {new Intl.NumberFormat("en-GB", {
                                        style: "currency",
                                        currency: "GBP",
                                    }).format(
                                        new BigNumber(total)
                                            .shiftedBy(-2)
                                            .toNumber(),
                                    )}
                                </div>
                            }
                        >
                            <BasicList
                                items={state.items.map((item) => {
                                    return {
                                        id: item.id,
                                        content: (
                                            <div className="flex justify-between items-center text-sm">
                                                <div>
                                                    <div>
                                                        {item.label} &times;
                                                        {item.quantity}
                                                    </div>
                                                    <div>
                                                        {new Intl.NumberFormat(
                                                            "en-GB",
                                                            {
                                                                style: "currency",
                                                                currency: "GBP",
                                                            },
                                                        ).format(
                                                            new BigNumber(
                                                                item.unit_amount,
                                                            )
                                                                .shiftedBy(-2)
                                                                .toNumber(),
                                                        )}
                                                        /item
                                                    </div>
                                                </div>
                                                <div className="flex gap-1">
                                                    <Button
                                                        variant="secondary"
                                                        onClick={() => {
                                                            dispatchItems({
                                                                type: "removeItem",
                                                                item: item,
                                                            });
                                                        }}
                                                    >
                                                        <MinusCircleIcon className="h-3" />
                                                    </Button>
                                                    <Button
                                                        variant="danger"
                                                        onClick={() => {
                                                            dispatchItems({
                                                                type: "removeAllOfItem",
                                                                item: item,
                                                            });
                                                        }}
                                                    >
                                                        <TrashIcon className="h-3" />
                                                    </Button>
                                                </div>
                                            </div>
                                        ),
                                    };
                                })}
                            ></BasicList>
                        </Card>

                        <Card title="Point of sale options">
                            <div className="grid gap-2">
                                <ButtonLink
                                    variant="secondary"
                                    href={route("index")}
                                >
                                    Exit
                                </ButtonLink>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>

            <Modal
                show={showWaiting}
                onClose={cancelCharge}
                title="Awaiting customer action"
                Icon={CreditCardIcon}
                buttons={
                    <Button variant="danger" onClick={cancelCharge}>
                        Cancel payment
                    </Button>
                }
            >
                <p className="mb-3">
                    Please wait for the customer to make payment.
                </p>
                <p>Closing this dialog will cancel the current action.</p>
            </Modal>

            <Modal
                show={showSuccess}
                onClose={clearAfterSuccess}
                variant="success"
                title="Payment successful"
                Icon={CheckCircleIcon}
                buttons={
                    <Button variant="success" onClick={clearAfterSuccess}>
                        Continue
                    </Button>
                }
            >
                <p>
                    Payment has been successful. Please ask the customer if they
                    would like to be sent a receipt by email.
                </p>
            </Modal>

            <Modal
                show={showError}
                onClose={clearAfterSuccess}
                variant="danger"
                title="Payment failed"
                Icon={ExclamationCircleIcon}
                buttons={
                    <>
                        <Button variant="primary" onClick={charge}>
                            Retry with other card
                        </Button>
                        <Button variant="secondary" onClick={clearReader}>
                            Abort
                        </Button>
                    </>
                }
            >
                <p className="mb-3">Payment failed.</p>
                <p>{readerStatus}</p>
            </Modal>

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
