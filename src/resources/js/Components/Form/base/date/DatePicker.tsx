import React from "react";
import { ClassNames, DayPicker } from "react-day-picker";
import { flip, offset } from "@floating-ui/dom";
import { getDisabledDays } from "@/Components/Form/base/date/DateInputUtils";
import { enGB } from "date-fns/locale";
import { Popover } from "@headlessui/react";
import styles from "react-day-picker/dist/style.module.css";
import Navbar from "@/Components/Form/base/date/Navbar";

const popoverMiddleware = [
    offset(3),
    flip({
        fallbackStrategy: "initialPlacement",
    }),
];

const DatePicker = React.forwardRef(
    (
        {
            inputElement,
            minDate,
            maxDate,
            selectedDays,
            disablePortal,
            onDayClick,
            pickerMouseDown,
            pickerProps,
            open,
        },
        ref
    ) => {
        const handleDayClick = (date, { disabled }, ev) => {
            console.log(date, { disabled }, ev);
            if (!disabled) {
                const { id, name } = inputElement?.current?.firstChild;
                ev.target = {
                    ...ev.target,
                    id,
                    name,
                };
                onDayClick(date, ev);
            }
        };

        if (!open) {
            return null;
        }

        const classNames: ClassNames = {
            ...styles,
            caption_label: "font-semibold text-gray-900",
            head: "",
            button: "",
            button_reset: "",
            row: "",
            tbody: "",
            cell: "",
            day: "mx-auto flex h-8 w-8 items-center justify-center rounded-full text-gray-900 hover:bg-gray-200",
            // day_range_start:
            //     "mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 font-semibold text-white",
            // day_range_middle:
            //     "mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 font-semibold text-white",
            // day_range_end:
            //     "mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 font-semibold text-white",
            day_selected:
                "mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 hover:bg-gray-900 font-semibold text-white",
            day_today:
                "mx-auto flex h-8 w-8 items-center justify-center rounded-full font-semibold text-indigo-600 hover:bg-gray-200",
        };

        return (
            <Popover>
                <Popover.Panel
                    static
                    // tabIndex={-1}
                    // style={popper.styles.popper}
                    className="dialog-sheet absolute z-10 mt-1 overflow-auto rounded-md bg-white p-3 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                    // ref={setPopperElement}
                    // role="dialog"
                >
                    <div ref={ref} onMouseDown={pickerMouseDown}>
                        <DayPicker
                            month={selectedDays}
                            onDayClick={handleDayClick}
                            // selectedDays={selectedDays}
                            selected={selectedDays}
                            date={selectedDays}
                            fixedWeeks
                            initialMonth={selectedDays || undefined}
                            disabledDays={getDisabledDays(minDate, maxDate)}
                            inline
                            locale={enGB}
                            showOutsideDays
                            {...pickerProps}
                            classNames={classNames}
                            components={{
                                Caption: Navbar,
                            }}
                        />
                    </div>
                </Popover.Panel>
            </Popover>
        );
    }
);

export default DatePicker;
