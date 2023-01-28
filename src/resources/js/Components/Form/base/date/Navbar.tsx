import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/24/outline";
import { useNavigation } from "react-day-picker";
import { format } from "date-fns";
import React from "react";
import { Popover } from "@headlessui/react";

const Navbar = (props) => {
    const { goToMonth, nextMonth, previousMonth } = useNavigation();
    return (
        <div className="text-center lg:col-start-8 lg:col-end-13 lg:row-start-1 xl:col-start-9">
            <div className="flex items-center text-gray-900">
                <Popover.Button
                    type="button"
                    disabled={!previousMonth}
                    onMouseDown={() => console.log("MOUSE DOWN")}
                    onClick={() => previousMonth && goToMonth(previousMonth)}
                    className="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500"
                >
                    <span className="sr-only">Previous month</span>
                    <ChevronLeftIcon className="h-5 w-5" aria-hidden="true" />
                </Popover.Button>
                <div className="flex-auto font-semibold">
                    {format(props.displayMonth, "MMMM yyy")}
                </div>
                <button
                    type="button"
                    disabled={!nextMonth}
                    onClick={() => nextMonth && goToMonth(nextMonth)}
                    className="-m-1.5 flex flex-none items-center justify-center p-1.5 text-gray-400 hover:text-gray-500"
                >
                    <span className="sr-only">Next month</span>
                    <ChevronRightIcon className="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>
        // <StyledNavbar {...props}>
        //     <StyledButton onClick={() => onPreviousClick()}>
        //         <Icon type="chevron_left" />
        //     </StyledButton>
        //     <StyledButton onClick={() => onNextClick()}>
        //         <Icon type="chevron_right" />
        //     </StyledButton>
        // </StyledNavbar>
    );
};

export default Navbar;
