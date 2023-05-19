import React, { useEffect, useMemo, useRef, useState } from "react";
// import "react-day-picker/dist/style.css";
// import "./DateInputOld.css";
import BaseInput from "@/Components/Form/base/BaseInput";
import DatePicker from "@/Components/Form/base/date/DatePicker";
import {
    additionalYears,
    checkISOFormatAndLength,
    findMatchedFormatAndValue,
    formattedValue,
    formatToISO,
    getSeparator,
    isDateValid,
    parseDate,
    parseISODate,
} from "@/Components/Form/base/date/DateInputUtils";
import { enGB } from "date-fns/locale";
import getFormatData from "@/Components/Form/base/date/DateInputFormats";
import useClickAwayListener from "@/Components/Form/base/useClickAwayListener";
import Events from "@/Components/Form/base/events";
import Input from "@/Components/Form/base/Input";
import { formatISO } from "date-fns/fp";

const DateInput = ({
    adaptiveLabelBreakpoint,
    allowEmptyValue,
    autoFocus,
    "data-component": dataComponent,
    "data-element": dataElement,
    "data-role": dataRole,
    disabled,
    disablePortal = false,
    helpAriaLabel,
    labelInline,
    minDate,
    maxDate,
    onBlur,
    onChange,
    onClick,
    onFocus,
    onKeyDown,
    pickerProps = {},
    readOnly,
    size = "medium",
    tooltipPosition,
    value = formatISO(Date.now()),
    className = "",
    leftText,
    rightButton,
    isInvalid,
    ...rest
}) => {
    const wrapperRef = useRef();
    const parentRef = useRef();
    const inputRef = useRef();
    const alreadyFocused = useRef(false);
    const isBlurBlocked = useRef(false);
    const focusedViaPicker = useRef(false);
    const { format, formats } = useMemo(() => getFormatData(enGB.code), [enGB]);
    // const { inputRefMap, setInputRefMap } = useContext(DateRangeContext);
    const inputName = dataElement?.split("-")[0];
    const [open, setOpen] = useState(false);
    const [selectedDays, setSelectedDays] = useState(
        checkISOFormatAndLength(value)
            ? parseISODate(value)
            : parseDate(format, value)
    );
    const isInitialValue = useRef(true);

    const computeInvalidRawValue = (inputValue) =>
        allowEmptyValue && !inputValue.length ? inputValue : null;

    const buildCustomEvent = (ev) => {
        const { id, name } = ev.target;

        const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
            ev.target.value,
            formats
        );

        const formattedValueString =
            ev.type === "blur"
                ? formattedValue(format, selectedDays)
                : ev.target.value;
        const rawValue = isDateValid(parseDate(matchedFormat, matchedValue))
            ? formatToISO(...additionalYears(matchedFormat, matchedValue))
            : computeInvalidRawValue(ev.target.value);

        ev.target = {
            ...(name && { name }),
            ...(id && { id }),
            value: {
                formattedValue: formattedValueString,
                rawValue,
            },
        };

        return ev;
    };

    const handleClickAway = () => {
        if (open) {
            alreadyFocused.current = true;
            inputRef.current.focus();
            isBlurBlocked.current = false;
            inputRef.current.blur();
            setOpen(false);
            alreadyFocused.current = false;
        }
    };

    const handleClickInside = useClickAwayListener(
        handleClickAway,
        "mousedown"
    );

    const handleChange = (ev) => {
        isInitialValue.current = false;
        onChange(buildCustomEvent(ev));
    };

    const focusInput = () => {
        focusedViaPicker.current = true;
        inputRef.current?.focus();
    };

    const handleDayClick = (day, ev) => {
        setSelectedDays(day);
        onChange(
            buildCustomEvent({
                ...ev,
                target: {
                    ...ev.target,
                    value: formattedValue(format, day),
                },
            })
        );
        focusInput();
        setOpen(false);
    };

    const handleBlur = (ev) => {
        if (disabled || readOnly) {
            return;
        }

        let event;

        if (isDateValid(selectedDays)) {
            event = buildCustomEvent(ev);

            const currentValue = checkISOFormatAndLength(value)
                ? formattedValue(format, parseISODate(value))
                : value;
            const [, matchedValue] = findMatchedFormatAndValue(
                currentValue,
                formats
            );

            if (formattedValue(format, selectedDays) !== matchedValue) {
                onChange(event);
            }
        } else {
            const { id, name } = ev.target;

            ev.target = {
                ...(name && { name }),
                ...(id && { id }),
                value: {
                    formattedValue: ev.target.value,
                    rawValue: computeInvalidRawValue(ev.target.value),
                },
            };

            event = ev;
        }

        if (isBlurBlocked.current) {
            return;
        }

        if (onBlur) {
            onBlur(event);
        }
    };

    const handleFocus = (ev) => {
        if (disabled || readOnly) {
            return;
        }

        isBlurBlocked.current = false;

        if (!open && !alreadyFocused.current) {
            setOpen(true);
        } else {
            alreadyFocused.current = false;
        }

        if (onFocus) {
            onFocus(ev);
        }
    };

    const handleKeyDown = (ev) => {
        if (onKeyDown) {
            onKeyDown(ev);
        }

        if (Events.isTabKey(ev)) {
            setOpen(false);
            alreadyFocused.current = false;
        }
    };

    const handleClick = (ev) => {
        if (disabled || readOnly) {
            return;
        }

        if (onClick) {
            onClick(ev);
        }
    };

    const handleMouseDown = (ev) => {
        handleClickInside(ev);

        if (disabled || readOnly) {
            return;
        }

        // if (setInputRefMap) {
        //     isBlurBlocked.current = true;
        // }

        if (ev.target.type === "text" && !open) {
            setOpen(true);
        } else if (ev.target.type !== "text") {
            alreadyFocused.current = true;
            setOpen((prev) => !prev);
        }
    };

    const handleIconMouseDown = (e) => {
        isBlurBlocked.current = true;
        handleMouseDown(e);
    };

    const handlePickerMouseDown = (ev) => {
        isBlurBlocked.current = true;
        handleClickInside(ev);
    };

    const assignInput = (input) => {
        inputRef.current = input.current;
        parentRef.current = input.current.parentElement;

        // if (inputRefMap && inputRefMap[inputName]?.setOpen !== setOpen) {
        //     setInputRefMap({
        //         [inputName]: { isBlurBlocked, setOpen },
        //     });
        // }
    };

    useEffect(() => {
        const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
            value,
            formats
        );

        if (
            matchedFormat &&
            matchedValue &&
            isDateValid(parseDate(matchedFormat, matchedValue))
        ) {
            setSelectedDays(
                parseDate(...additionalYears(matchedFormat, matchedValue))
            );
        } else if (checkISOFormatAndLength(value) && isInitialValue.current) {
            setSelectedDays(parseISODate(value));
        } else {
            setSelectedDays(undefined);
        }
    }, [value, formats]);

    const computedValue = () => {
        if (checkISOFormatAndLength(value) && isInitialValue.current) {
            return formattedValue(format, parseISODate(value));
        }

        const valueSeparator = getSeparator(value);
        const formatSeparator = getSeparator(format);
        const replaceSeparators = () =>
            value
                .split("")
                .map((char) =>
                    char === valueSeparator ? formatSeparator : char
                )
                .join("");

        if (
            isInitialValue.current &&
            valueSeparator !== formatSeparator &&
            isDateValid(parseDate(format, replaceSeparators()))
        ) {
            isInitialValue.current = false;

            const [matchedFormat, matchedValue] = findMatchedFormatAndValue(
                replaceSeparators(),
                formats
            );
            return formattedValue(
                format,
                parseDate(...additionalYears(matchedFormat, matchedValue))
            );
        }

        return value;
    };

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
    }

    if (!leftText) {
        className += " rounded-l-md ";
    }

    if (!rightButton) {
        className += " rounded-r-md ";
    }

    return (
        <div
            ref={wrapperRef}
            role="presentation"
            // size={size}
            // labelInline={labelInline}
            data-component={dataComponent || "date"}
            data-element={dataElement}
            data-role={dataRole}
            // applyDateRangeStyling={!!inputRefMap}
        >
            <BaseInput
                input={
                    <Input
                        type="text"
                        className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                        value={computedValue()}
                        onBlur={handleBlur}
                        onChange={handleChange}
                        onClick={handleClick}
                        onFocus={handleFocus}
                        onKeyDown={handleKeyDown}
                        onMouseDown={handleMouseDown}
                        inputRef={assignInput}
                        // tooltipPosition={tooltipPosition}
                        // helpAriaLabel={helpAriaLabel}
                        autoFocus={autoFocus}
                        disabled={disabled}
                        readOnly={readOnly}
                    />
                }
            />
            <DatePicker
                inputElement={parentRef}
                pickerProps={pickerProps}
                selectedDays={selectedDays}
                setSelectedDays={setSelectedDays}
                onDayClick={handleDayClick}
                minDate={minDate}
                maxDate={maxDate}
                pickerMouseDown={handlePickerMouseDown}
                open={open}
            />
        </div>
    );
};

export default DateInput;
